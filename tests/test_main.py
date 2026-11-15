import io
import os
import tempfile
import unittest
from unittest.mock import patch

from bson import ObjectId

import main


class FakeProducts:
    def __init__(self, products=None):
        self.products = list(products) if products else [{
            "_id": ObjectId(),
            "slug": "test-item",
            "p_name": "Test item",
            "p_price": "125.50",
            "p_details": "A test product",
            "img_file": "shirt.jpg",
            "category": "T-Shirts",
        }]
        self.product = self.products[0]

    def find_one(self, query):
        if query.get("_id") == self.product["_id"] or query.get("slug") == self.product["slug"]:
            return self.product
        return None

    def find(self):
        return list(self.products)

    def insert_many(self, entries):
        self.products.extend(entries)
        return None

    def insert_one(self, entry):
        self.products.append(entry)
        return None


class FakeContacts:
    def __init__(self):
        self.entries = []

    def insert_one(self, entry):
        self.entries.append(entry)


class MainAppTests(unittest.TestCase):
    def setUp(self):
        self.products = FakeProducts()
        self.contacts = FakeContacts()
        self.products_patch = patch.object(main, "products_collection", self.products)
        self.contacts_patch = patch.object(main, "contacts_collection", self.contacts)
        self.products_patch.start()
        self.contacts_patch.start()
        self.addCleanup(self.products_patch.stop)
        self.addCleanup(self.contacts_patch.stop)
        main.app.config["TESTING"] = True
        self.client = main.app.test_client()
        self.mail_patch = patch.object(main.mail, "send_message")
        self.send_message = self.mail_patch.start()
        self.addCleanup(self.mail_patch.stop)

    def test_contact_message_is_saved_and_sent_to_configured_inbox(self):
        response = self.client.post(
            "/contact",
            data={
                "name": "Customer",
                "email": "customer@example.com",
                "phone": "123456789",
                "message": "Is this item available?",
            },
            follow_redirects=True,
        )

        self.assertEqual(response.status_code, 200)
        self.assertIn(b"Message sent successfully!", response.data)
        self.assertIn(b'class="flash-messages"', response.data)
        self.assertLess(
            response.data.index(b'class="flash-messages"'),
            response.data.index(b'class="glass-card"'),
        )
        self.assertIn(main.app.config["CONTACT_RECIPIENT"].encode(), response.data)
        self.assertEqual(len(self.contacts.entries), 1)
        self.send_message.assert_called_once()
        sent_message = self.send_message.call_args.kwargs
        self.assertEqual(sent_message["recipients"], [main.app.config["CONTACT_RECIPIENT"]])
        self.assertEqual(sent_message["reply_to"], "customer@example.com")
        self.assertNotIn("sender", sent_message)
        self.assertEqual(main.app.config["MAIL_DEFAULT_SENDER"], main.app.config["MAIL_USERNAME"])

    def test_contact_page_uses_configured_recipient(self):
        response = self.client.get("/contact")
        self.assertEqual(response.status_code, 200)
        self.assertIn(main.app.config["CONTACT_RECIPIENT"].encode(), response.data)

    def test_contact_email_failure_is_reported_without_claiming_success(self):
        self.send_message.side_effect = OSError("SMTP unavailable")

        response = self.client.post(
            "/contact",
            data={
                "name": "Customer",
                "email": "customer@example.com",
                "phone": "123456789",
                "message": "Please call me.",
            },
            follow_redirects=True,
        )

        self.assertEqual(response.status_code, 200)
        self.assertIn(b"email notification could not be sent", response.data)
        self.assertEqual(len(self.contacts.entries), 1)

    def test_product_can_be_added_and_removed_from_cart(self):
        product_id = str(self.products.product["_id"])
        product_page = self.client.get("/product/test-item")
        self.assertEqual(product_page.status_code, 200)
        self.assertIn(f"/add_to_cart/{product_id}".encode(), product_page.data)

        response = self.client.post(f"/add_to_cart/{product_id}")
        self.assertEqual(response.status_code, 302)
        self.client.post(f"/add_to_cart/{product_id}")
        with self.client.session_transaction() as browser_session:
            self.assertEqual(browser_session["cart"][0]["quantity"], 2)

        response = self.client.post(f"/remove_from_cart/{product_id}")
        self.assertEqual(response.status_code, 302)
        with self.client.session_transaction() as browser_session:
            self.assertEqual(browser_session["cart"], [])

    def test_invalid_product_id_returns_not_found(self):
        response = self.client.post("/add_to_cart/not-an-object-id")
        self.assertEqual(response.status_code, 404)

    def test_invalid_product_price_is_rejected(self):
        self.products.product["p_price"] = "nan"
        response = self.client.post(f"/add_to_cart/{self.products.product['_id']}")
        self.assertEqual(response.status_code, 400)

    def test_shop_search_and_category_filters_work(self):
        response = self.client.get("/shop?q=test&category=T-Shirts")
        self.assertEqual(response.status_code, 200)
        self.assertIn(b"Test item", response.data)

    def test_cart_quantity_update_works(self):
        self.client.post(f"/add_to_cart/{self.products.product['_id']}")
        response = self.client.post(f"/update_cart_quantity/{self.products.product['_id']}", data={"quantity": 3})
        self.assertEqual(response.status_code, 302)
        with self.client.session_transaction() as browser_session:
            self.assertEqual(browser_session["cart"][0]["quantity"], 3)

    def test_demo_products_are_only_seeded_once(self):
        collection = FakeProducts([
            {"p_name": "Existing item", "slug": "existing-item", "p_price": "100", "p_details": "Already here", "category": "Shirts"}
        ])
        with patch.object(main, "products_collection", collection):
            main.ensure_demo_products()
            self.assertEqual(len(collection.products), 1)

    def test_production_config_rejects_placeholder_secret(self):
        with (
            patch.object(main, "IS_PRODUCTION", True),
            patch.dict(main.app.config, {"SECRET_KEY": "change-me-in-production"}),
            patch.dict(main.params, {
                "admin_password": "a-secure-test-password",
                "mongo_uri": "mongodb+srv://cluster.example.mongodb.net/thrift_store",
                "razorpay_key_id": "rzp_test_example",
                "razorpay_key_secret": "test-secret",
            }),
        ):
            with self.assertRaisesRegex(RuntimeError, "SECRET_KEY"):
                main.validate_production_config()

    def test_uploaded_product_images_are_saved_in_upload_directory(self):
        with tempfile.TemporaryDirectory() as upload_dir:
            with patch.dict(main.app.config, {"UPLOAD_FOLDER": upload_dir}):
                self.client.post(
                    "/login",
                    data={
                        "username": main.params["admin_username"],
                        "password": main.params["admin_password"],
                    },
                )
                response = self.client.post(
                    "/admin",
                    data={
                        "name": "Uploaded Shirt",
                        "price": "250",
                        "details": "Uploaded item",
                        "slug": "uploaded-shirt",
                        "category": "Shirts",
                        "img_file": (io.BytesIO(b"image-bytes"), "shirt.jpg"),
                    },
                    content_type="multipart/form-data",
                )

                self.assertEqual(response.status_code, 302)
                uploaded_product = self.products.products[-1]
                self.assertTrue(uploaded_product["img_file"].startswith("uploads/"))
                stored_file = os.path.join(upload_dir, os.path.basename(uploaded_product["img_file"]))
                with open(stored_file, "rb") as image_file:
                    self.assertEqual(image_file.read(), b"image-bytes")

    def test_admin_can_login_and_reach_dashboard(self):
        response = self.client.post(
            "/login",
            data={
                "username": main.params["admin_username"],
                "password": main.params["admin_password"],
            },
        )

        self.assertEqual(response.status_code, 302)
        dashboard = self.client.get("/dashboard")
        self.assertEqual(dashboard.status_code, 200)
        product_id = str(self.products.product["_id"])
        self.assertIn(f"/admin/edit/{product_id}".encode(), dashboard.data)
        self.assertIn(f"/admin/delete/{product_id}".encode(), dashboard.data)
        edit_page = self.client.get(f"/admin/edit/{product_id}")
        self.assertEqual(edit_page.status_code, 200)


if __name__ == "__main__":
    unittest.main()
