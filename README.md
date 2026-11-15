# ANSH THRIFTS
Pre-Loved Fashion, New Stories.

ANSH THRIFTS is a Flask-based thrift-store storefront with MongoDB-backed product management, cart checkout, contact form handling, and admin product operations. The app is intentionally kept in Razorpay test mode until the deployment environment is configured.

## Features

- Responsive storefront pages for home, shop, product detail, cart, contact, and about sections
- Product search and category filtering
- Shopping cart with quantity updates and total calculations
- Checkout flow using Razorpay in test mode
- Admin login/logout and secure dashboard access
- Add, edit, and delete product operations with image upload support
- Contact form message storage and email notification support
- Safe demo product seeding without overwriting existing product records

## Tech stack

- Python 3.12+
- Flask
- Flask-Login
- Flask-Mail
- Flask-PyMongo
- MongoDB
- Razorpay (test mode only)
- Bootstrap + Jinja2 templates

## Project structure

```text
ANSH THRIFTS/
├── main.py
├── config.json
├── requirements.txt
├── .env.example
├── .gitignore
├── static/
│   ├── css/
│   ├── js/
│   └── images/
├── templates/
│   ├── layout.html
│   ├── index.html
│   ├── shop.html
│   ├── product.html
│   ├── cart.html
│   ├── contact.html
│   ├── about.html
│   ├── login.html
│   ├── dashboard.html
│   ├── admin_dashboard.html
│   ├── edit_product.html
│   └── ...
├── tests/
│   └── test_main.py
└── README.md
```

## Local setup

### Prerequisites

- Python 3.12+
- MongoDB running locally or reachable via a connection string
- A Gmail account for mail delivery if the contact form is enabled
- A workspace with access to your environment variables

### 1) Create a virtual environment

```bash
python -m venv .venv
source .venv/bin/activate
```

### 2) Install project dependencies

```bash
pip install -r requirements.txt
```

### 3) Configure environment variables

Create a local `.env` file from the example template:

```bash
cp .env.example .env
```

Then update `.env` with your actual values before running the app.

Required variables:

```env
SECRET_KEY=your-long-random-secret
SESSION_COOKIE_SECURE=0
FLASK_DEBUG=0
PORT=5000
MONGO_URI=mongodb://localhost:27017/thrift_store
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-google-app-password
CONTACT_RECIPIENT=your-email@gmail.com
ADMIN_USERNAME=admin
ADMIN_PASSWORD=your-secure-admin-password
RAZORPAY_KEY_ID=rzp_test_your_key_id
RAZORPAY_KEY_SECRET=your_test_key_secret
```

Important:
- Never commit `.env` to source control.
- Use a strong admin password and a production-grade secret key in deployment.
- Keep Razorpay in test mode unless deliberately switching to live payment processing, which is not enabled in this project.

### 4) Start MongoDB

Make sure MongoDB is running and reachable from the configured `MONGO_URI`.

### 5) Run the app

```bash
cd "/home/prem/Prem Projects/ANSH THRIFTS"
python3 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
python main.py
```

Then open:

```text
http://localhost:5000
```

## Deployment guidance

### Render Web Service

Create a new **Python Web Service** connected to this repository. Do not configure an automatic deploy until the required secrets, database access, and persistent disk are ready.

Use this configuration:

| Render setting | Value |
|---|---|
| Runtime | Python |
| Build command | `pip install -r requirements.txt` |
| Start command | `gunicorn --bind 0.0.0.0:$PORT main:app` |
| Health check path | `/login` |
| Persistent disk mount path | `/opt/render/project/src/static/images/uploads` |

Attach a Render persistent disk at the mount path above. User-uploaded files are stored under that directory and the application records them as `uploads/<filename>`, which keeps the current static image URLs working. The disk preserves new uploads across deploys; keep bundled images in `static/images` outside the mount. Render disks are a paid feature. If the selected Render plan does not support disks or you need multiple app instances, integrate an object-storage service (for example S3-compatible storage) before deployment; local filesystem storage is not shared across instances.

Set these Render environment variables. Enter secrets directly in Render's environment settings; do not put real values in the repository or deployment logs.

| Variable | Required value |
|---|---|
| `APP_ENV` | `production` |
| `FLASK_DEBUG` | `0` |
| `SESSION_COOKIE_SECURE` | `1` |
| `SECRET_KEY` | Generate a unique random value of at least 32 characters, for example with `python -c "import secrets; print(secrets.token_hex(32))"` |
| `ADMIN_USERNAME` | Your chosen admin username |
| `ADMIN_PASSWORD` | A unique password of at least 12 characters |
| `MONGO_URI` | MongoDB Atlas SRV URI for the `thrift_store` database |
| `RAZORPAY_KEY_ID` | A Razorpay test-mode key ID beginning with `rzp_test_` |
| `RAZORPAY_KEY_SECRET` | The matching Razorpay test-mode secret |
| `MAIL_USERNAME` | SMTP sender account, if contact email delivery is enabled |
| `MAIL_PASSWORD` | SMTP/App Password, if contact email delivery is enabled |
| `CONTACT_RECIPIENT` | Inbox for customer messages; defaults to `MAIL_USERNAME` |

Render provides `PORT`; do not set it manually unless required by the service configuration. The app refuses production startup if the secret/admin password is still a placeholder, the Mongo URI is not an Atlas `mongodb+srv://` URI, or Razorpay credentials are not test credentials.

### MongoDB Atlas setup

1. Create an Atlas cluster and a database user with only the permissions needed by the app.
2. Create the `thrift_store` database through the URI path. Collections are created by MongoDB as the application writes them.
3. Add the Render service's outbound IP addresses to Atlas Network Access. Prefer fixed/known egress IPs where available; avoid broad public access unless there is no narrower alternative.
4. Put the SRV connection string in Render's `MONGO_URI` environment variable. URL-encode special characters in the database username/password.
5. Do not put credentials in `config.json`, `.env.example`, or source control.

### Before enabling the service

- Confirm all required Render variables are set; production startup checks reject placeholder credentials.
- Keep Razorpay in test mode. Live payment keys are rejected by the production configuration check.
- Verify Atlas connectivity and SMTP delivery after deployment; neither external service is exercised by the local unit tests.
- Confirm the persistent disk is mounted and an uploaded image is still available after a service restart.
- Use Render's deploy controls manually. This project does not deploy or publish itself.

## Security notes

- Do not store secrets in the repository; use deployment environment settings.
- `.env` is ignored by Git.
- Uploaded product images are written to `static/images/uploads`; use the Render persistent disk mount documented above to persist them.
- Admin credentials must be provided through environment variables.
- Session cookies are configured to be HTTP-only and SameSite-Lax.
- Production also requires HTTPS secure cookies and strong environment-supplied secrets; Flask-Login session protection is enabled.
- Demo products are inserted only when the product collection is empty.

## Contact form and emails

The contact form stores submissions in the MongoDB `contacts` collection and attempts to send an email using the configured `MAIL_USERNAME` and `MAIL_PASSWORD`. Gmail may require an App Password rather than the main account password.

## Testing

Run the project test suite with:

```bash
pytest
```

The app is expected to pass the included test checks for cart operations, admin access, contact form handling, and demo-product seeding.

## Notes

- This project does not automatically publish or deploy to any hosting provider.
- Real Razorpay payments are intentionally not activated.
- Existing product records are not overwritten by the demo seeding logic.
