from flask import Flask, abort, render_template, request, redirect, url_for, flash, session, jsonify
from flask_pymongo import PyMongo
from flask_mail import Mail
from flask_login import LoginManager, UserMixin, login_user, logout_user, login_required
from werkzeug.utils import secure_filename
from bson.objectid import ObjectId
from bson.errors import InvalidId
from datetime import datetime
from dotenv import load_dotenv
import json
import math
import os
import razorpay
import smtplib
from uuid import uuid4

load_dotenv()

# Load configuration from config.json
with open(os.path.join(os.path.dirname(__file__), 'config.json'), 'r') as file:
    config_data = json.load(file)
    params = config_data["params"]

# Flask app setup
app = Flask(__name__)
IS_PRODUCTION = os.environ.get("APP_ENV", "development").lower() == "production"
app.config["SECRET_KEY"] = os.environ.get("SECRET_KEY") or "change-me-in-production"
app.config["DEBUG"] = False
app.config["SESSION_COOKIE_HTTPONLY"] = True
app.config["SESSION_COOKIE_SAMESITE"] = "Lax"
app.config["SESSION_COOKIE_SECURE"] = IS_PRODUCTION or os.environ.get("SESSION_COOKIE_SECURE", "0") == "1"
app.config["UPLOAD_FOLDER"] = os.path.join(app.root_path, "static", "images", "uploads")
os.makedirs(app.config["UPLOAD_FOLDER"], exist_ok=True)
APP_NAME = params.get("blog_name", "ANSH THRIFTS")
APP_TAGLINE = params.get("tag_line", "Pre-Loved Fashion, New Stories.")

# Environment-backed runtime config
params["mongo_uri"] = os.environ.get("MONGO_URI", params.get("mongo_uri", "mongodb://localhost:27017/thrift_store"))
params["admin_username"] = os.environ.get("ADMIN_USERNAME", params.get("admin_username", "admin"))
params["admin_password"] = os.environ.get("ADMIN_PASSWORD", params.get("admin_password", "change-me-in-production"))
params["razorpay_key_id"] = os.environ.get("RAZORPAY_KEY_ID", params.get("razorpay_key_id", "rzp_test_your_key_id"))
params["razorpay_key_secret"] = os.environ.get("RAZORPAY_KEY_SECRET", params.get("razorpay_key_secret", "your_test_key_secret"))


def validate_production_config():
    if not IS_PRODUCTION:
        return

    secret_key = app.config["SECRET_KEY"]
    admin_password = params["admin_password"]
    mongo_uri = params["mongo_uri"]
    razorpay_key_id = params["razorpay_key_id"]
    razorpay_key_secret = params["razorpay_key_secret"]

    if len(secret_key) < 32 or secret_key == "change-me-in-production":
        raise RuntimeError("Production requires a SECRET_KEY with at least 32 characters.")
    if len(admin_password) < 12 or admin_password == "change-me-in-production":
        raise RuntimeError("Production requires a strong ADMIN_PASSWORD with at least 12 characters.")
    if not mongo_uri.startswith("mongodb+srv://"):
        raise RuntimeError("Production requires a MongoDB Atlas mongodb+srv:// MONGO_URI.")
    if (
        not razorpay_key_id.startswith("rzp_test_")
        or razorpay_key_id == "rzp_test_your_key_id"
        or not razorpay_key_secret
        or razorpay_key_secret == "your_test_key_secret"
    ):
        raise RuntimeError("Production is configured for Razorpay test mode and requires valid test credentials.")


validate_production_config()

# MongoDB configuration
app.config["MONGO_URI"] = params["mongo_uri"]
mongo = PyMongo(app)

# Razorpay client initialization
razorpay_client = razorpay.Client(auth=(params["razorpay_key_id"], params["razorpay_key_secret"]))

# Mail configuration
app.config.update(
    MAIL_SERVER='smtp.gmail.com',
    MAIL_PORT=587,
    MAIL_USE_TLS=True,
    MAIL_USERNAME=os.environ.get("MAIL_USERNAME", params.get("gmail-user", "")),
    MAIL_PASSWORD=os.environ.get("MAIL_PASSWORD", ""),
    MAIL_DEFAULT_SENDER=os.environ.get("MAIL_USERNAME", params.get("gmail-user", "")),
    CONTACT_RECIPIENT=os.environ.get(
        "CONTACT_RECIPIENT",
        os.environ.get("MAIL_USERNAME", params.get("gmail-user", ""))
    )
)
mail = Mail(app)

# Flask-Login setup
login_manager = LoginManager(app)
login_manager.login_view = 'login'
login_manager.session_protection = "strong"

# MongoDB Collections
users_collection = mongo.db.users
products_collection = mongo.db.products
contacts_collection = mongo.db.contacts


def ensure_demo_products():
    products = list(products_collection.find())

    if products:
        return

    demo_products = [
        {
            "p_name": "Vintage Denim Jacket",
            "p_price": "699",
            "p_details": "A timeless denims piece with a relaxed vintage fit and durable finish.",
            "slug": "vintage-denim-jacket",
            "img_file": "jeans.jpg",
            "category": "Jackets"
        },
        {
            "p_name": "Classic Oversized T-Shirt",
            "p_price": "299",
            "p_details": "Soft cotton comfort with an oversized silhouette for everyday wear.",
            "slug": "classic-oversized-t-shirt",
            "img_file": "shirt.jpg",
            "category": "T-Shirts"
        },
        {
            "p_name": "Retro Blue Jeans",
            "p_price": "499",
            "p_details": "Easy-fit jeans with a retro wash and classic straight leg design.",
            "slug": "retro-blue-jeans",
            "img_file": "jeans.jpg",
            "category": "Jeans"
        },
        {
            "p_name": "Vintage Graphic Shirt",
            "p_price": "399",
            "p_details": "A standout graphic piece that blends nostalgia and street-ready styling.",
            "slug": "vintage-graphic-shirt",
            "img_file": "shirt.jpg",
            "category": "Shirts"
        },
        {
            "p_name": "Casual Cotton Shirt",
            "p_price": "349",
            "p_details": "Breathable everyday shirt with a clean finish for casual layering.",
            "slug": "casual-cotton-shirt",
            "img_file": "shirt.jpg",
            "category": "Shirts"
        },
        {
            "p_name": "Streetwear Hoodie",
            "p_price": "599",
            "p_details": "Cozy fleece hoodie designed for comfort, movement, and laid-back style.",
            "slug": "streetwear-hoodie",
            "img_file": "product3.jpg",
            "category": "Hoodies"
        },
        {
            "p_name": "Vintage Denim Shirt",
            "p_price": "449",
            "p_details": "A sturdy washed denim shirt with a relaxed fit and heritage appeal.",
            "slug": "vintage-denim-shirt",
            "img_file": "shirt.jpg",
            "category": "Shirts"
        },
        {
            "p_name": "Classic Black T-Shirt",
            "p_price": "249",
            "p_details": "Minimal black tee with premium softness and effortless versatility.",
            "slug": "classic-black-t-shirt",
            "img_file": "tshirt.jpeg",
            "category": "T-Shirts"
        },
        {
            "p_name": "Retro Casual Jeans",
            "p_price": "549",
            "p_details": "A relaxed, casual fit with an easy-wear silhouette and vintage detail.",
            "slug": "retro-casual-jeans",
            "img_file": "jeans.jpg",
            "category": "Jeans"
        },
        {
            "p_name": "Vintage Everyday Shirt",
            "p_price": "329",
            "p_details": "A classic staple shirt made for daily use with a pre-loved character.",
            "slug": "vintage-everyday-shirt",
            "img_file": "shirt.jpg",
            "category": "Shirts"
        },
        {
            "p_name": "Minimalist Oversized Tee",
            "p_price": "299",
            "p_details": "Simple, clean, and easy to style for everyday thrift-store dressing.",
            "slug": "minimalist-oversized-tee",
            "img_file": "tshirt.jpeg",
            "category": "T-Shirts"
        },
        {
            "p_name": "Classic Denim Collection",
            "p_price": "649",
            "p_details": "A refined denim staple with unique wear and a versatile thrifted finish.",
            "slug": "classic-denim-collection",
            "img_file": "jeans.jpg",
            "category": "Jeans"
        }
    ]

    if hasattr(products_collection, "insert_many"):
        products_collection.insert_many(demo_products)


def save_product_image(file):
    safe_filename = secure_filename(file.filename)
    if not safe_filename:
        abort(400, description="A valid image filename is required.")

    filename = f"{uuid4().hex}_{safe_filename}"
    file.save(os.path.join(app.config["UPLOAD_FOLDER"], filename))
    return f"uploads/{filename}"


def get_product_or_404(product_id):
    try:
        object_id = ObjectId(product_id)
    except InvalidId:
        abort(404)
    product = products_collection.find_one({"_id": object_id})
    if product is None:
        abort(404)
    return object_id, product


# User loader for Flask-Login
class AdminUser(UserMixin):
    def __init__(self, username):
        self.id = username


@login_manager.user_loader
def load_user(user_id):
    if user_id == params["admin_username"]:
        return AdminUser(user_id)
    return None

# Home route
@app.route('/')
def home():
    ensure_demo_products()
    products = list(products_collection.find())
    return render_template('index.html', products=products, app_name=APP_NAME, app_tagline=APP_TAGLINE)

# Login route
@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form.get('username')
        password = request.form.get('password')

        if username == params["admin_username"] and password == params["admin_password"]:
            login_user(AdminUser(username))
            session['user'] = username
            flash("Login successful!", "success")
            return redirect(url_for('dashboard'))
        flash("Invalid username or password.", "danger")
    return render_template('login.html', params=params)

# Dashboard route
@app.route('/dashboard')
@login_required
def dashboard():
    if session.get('user') != params['admin_username']:
        flash("Access denied. Please log in as admin.", "warning")
        return redirect(url_for('login'))
    products = list(products_collection.find())
    return render_template('dashboard.html', products=products)

# Add product route
@app.route('/admin', methods=['GET', 'POST'])
@login_required
def admin():
    if request.method == 'POST':
        name = request.form['name']
        price = request.form['price']
        details = request.form['details']
        slug = request.form['slug']
        category = (request.form.get('category') or '').strip() or 'General'
        file = request.files.get('img_file')

        if file and file.filename:
            image_path = save_product_image(file)
            new_product = {
                "p_name": name,
                "p_price": price,
                "p_details": details,
                "slug": slug,
                "img_file": image_path,
                "category": category
            }
            products_collection.insert_one(new_product)
            flash("Product added successfully!", "success")
            return redirect(url_for('dashboard'))

    return render_template('admin_dashboard.html')

# Edit product route
@app.route('/admin/edit/<string:product_id>', methods=['GET', 'POST'])
@login_required
def edit_product(product_id):
    object_id, product = get_product_or_404(product_id)

    if request.method == 'POST':
        name = request.form['name']
        price = request.form['price']
        details = request.form['details']
        category = (request.form.get('category') or '').strip() or 'General'
        file = request.files.get('img_file')

        update_data = {
            "p_name": name,
            "p_price": price,
            "p_details": details,
            "category": category
        }
        if file and file.filename:
            update_data["img_file"] = save_product_image(file)

        products_collection.update_one({"_id": object_id}, {"$set": update_data})
        flash("Product updated successfully!", "success")
        return redirect(url_for('dashboard'))

    return render_template('edit_product.html', product=product)

# Delete product route
@app.route('/admin/delete/<string:product_id>', methods=['POST'])
@login_required
def delete_product(product_id):
    object_id, _ = get_product_or_404(product_id)
    products_collection.delete_one({"_id": object_id})
    flash("Product deleted successfully!", "success")
    return redirect(url_for('dashboard'))

# Logout route
@app.route('/logout')
def logout():
    session.pop('user', None)
    logout_user()
    flash("Logged out successfully!", "success")
    return redirect(url_for('login'))

# Product Page Route
@app.route('/product/<string:slug>', methods=['GET', 'POST'])
def product_page(slug):
    product = products_collection.find_one({"slug": slug})
    if product is None:
        abort(404)
    return render_template('product.html', product=product)


@app.route('/add_to_cart/<string:product_id>', methods=['POST'])
def add_to_cart(product_id):
    _, product = get_product_or_404(product_id)

    try:
        price = float(product["p_price"])
    except (KeyError, TypeError, ValueError):
        abort(400, description="Product has an invalid price.")
    if not math.isfinite(price) or price < 0:
        abort(400, description="Product has an invalid price.")

    cart = session.get("cart", [])
    item_id = str(product["_id"])
    for item in cart:
        if item["id"] == item_id:
            item["quantity"] += 1
            break
    else:
        cart.append({
            "id": item_id,
            "name": product["p_name"],
            "price": price,
            "image": product.get("img_file", ""),
            "quantity": 1
        })

    session["cart"] = cart
    flash(f"{product['p_name']} added to cart!", "success")
    return redirect(url_for("cart"))


@app.route('/remove_from_cart/<string:p_no>', methods=['POST'], endpoint='remove_from_cart_2')
def remove_from_cart(p_no):
    cart = session.get("cart", [])
    session["cart"] = [item for item in cart if item.get("id") != p_no]
    return redirect(url_for("cart"))


@app.route('/update_cart_quantity/<string:item_id>', methods=['POST'])
def update_cart_quantity(item_id):
    cart = session.get('cart', [])
    quantity = request.form.get('quantity', '1')
    try:
        quantity = int(quantity)
    except (TypeError, ValueError):
        quantity = 1
    if quantity <= 0:
        cart = [item for item in cart if item.get('id') != item_id]
    else:
        for item in cart:
            if item.get('id') == item_id:
                item['quantity'] = quantity
                break
    session['cart'] = cart
    return redirect(url_for('cart'))


@app.route('/contact', methods=['GET', 'POST'])
def contact():
    if request.method == 'POST':
        name = (request.form.get('name') or "").strip()
        email = (request.form.get('email') or "").strip()
        phone_no = (request.form.get('phone') or "").strip()
        msg = (request.form.get('message') or "").strip()
        if not all((name, email, phone_no, msg)):
            flash("Please complete all contact form fields.", "danger")
            return redirect(url_for('contact'))

        date = datetime.now().strftime("%Y-%m-%d")

        contact = {
            "name": name,
            "email": email,
            "phone_no": phone_no,
            "msg": msg,
            "date": date
        }
        contacts_collection.insert_one(contact)
        try:
            mail.send_message(
                subject="New contact message from Thrift Store",
                recipients=[app.config["CONTACT_RECIPIENT"]],
                reply_to=email,
                body=f"Name: {name}\nEmail: {email}\nPhone: {phone_no}\n\nMessage:\n{msg}"
            )
        except (smtplib.SMTPException, OSError):
            app.logger.exception("Could not send contact form email")
            flash("Your message was saved, but its email notification could not be sent.", "warning")
        else:
            flash("Message sent successfully!", "success")
        return redirect(url_for('contact'))

    return render_template('contact.html', contact_email=app.config["CONTACT_RECIPIENT"])

@app.route('/shop')
def shop():
    ensure_demo_products()
    search = (request.args.get('q') or '').strip()
    category = (request.args.get('category') or '').strip()
    products = list(products_collection.find())
    if search:
        search_lower = search.lower()
        products = [
            product for product in products
            if search_lower in (product.get('p_name', '') + ' ' + product.get('p_details', '') + ' ' + product.get('category', '')).lower()
        ]
    if category:
        products = [product for product in products if str(product.get('category', '')).lower() == category.lower()]
    return render_template('shop.html', products=products, app_name=APP_NAME, app_tagline=APP_TAGLINE)

@app.route('/about')
def about():
    return render_template('about.html')

@app.route('/cart')
def cart():
    cart = session.get('cart', [])
    total_price = sum(float(item['price']) * item['quantity'] for item in cart)
    return render_template('cart.html', cart=cart, total_price=total_price, razorpay_key_id=params["razorpay_key_id"])

# Create Razorpay Order
@app.route('/create_order', methods=['POST'])
def create_order():
    cart = session.get('cart', [])
    if not cart:
        return jsonify({'error': 'Cart is empty'}), 400
    
    total_price = sum(float(item['price']) * item['quantity'] for item in cart)
    amount_in_paise = int(total_price * 100)  # Razorpay expects amount in paise
    
    try:
        order_data = {
            'amount': amount_in_paise,
            'currency': 'INR',
            'receipt': f'order_{datetime.now().strftime("%Y%m%d%H%M%S")}',
            'payment_capture': 1  # Auto-capture payment
        }
        order = razorpay_client.order.create(data=order_data)
        return jsonify({
            'order_id': order['id'],
            'amount': order['amount'],
            'currency': order['currency'],
            'key_id': params["razorpay_key_id"]
        })
    except Exception as e:
        return jsonify({'error': str(e)}), 500

# Verify Payment
@app.route('/verify_payment', methods=['POST'])
def verify_payment():
    data = request.get_json()
    razorpay_order_id = data.get('razorpay_order_id')
    razorpay_payment_id = data.get('razorpay_payment_id')
    razorpay_signature = data.get('razorpay_signature')
    
    try:
        # Verify the payment signature
        params_dict = {
            'razorpay_order_id': razorpay_order_id,
            'razorpay_payment_id': razorpay_payment_id,
            'razorpay_signature': razorpay_signature
        }
        razorpay_client.utility.verify_payment_signature(params_dict)
        
        # Payment verified successfully - clear cart and save order
        session.pop('cart', None)
        
        # Optionally save order to database here
        # order_collection.insert_one({...})
        
        return jsonify({'success': True, 'message': 'Payment verified successfully!'})
    except razorpay.errors.SignatureVerificationError:
        return jsonify({'success': False, 'error': 'Payment verification failed'}), 400
    except Exception as e:
        return jsonify({'success': False, 'error': str(e)}), 500

# Run the app
if __name__ == '__main__':
    app.run(
        host="0.0.0.0",
        port=int(os.environ.get("PORT", "5000")),
        debug=not IS_PRODUCTION and os.environ.get("FLASK_DEBUG", "0") == "1"
    )
