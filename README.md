agri_marketplace/
│
├── config/
│   └── Database.php              # Database connection (PDO)
│
├── models/
│   ├── User.php                  # Parent user class
│   ├── Farmer.php                # Farmer model
│   ├── Buyer.php                 # Buyer model
│   ├── Driver.php                # Driver model
│   ├── Agronomist.php            # Agronomist model
│   └── Product.php               # Product model
│
├── controllers/
│   ├── AuthController.php        # Registration, login, email verification
│   ├── ProductController.php     # CRUD for products
│   ├── BidController.php         # Buyers place bids
│   └── PaymentController.php     # Mpesa escrow logic
│
├── views/
│   ├── auth/
│   │   ├── register.php
│   │   ├── login.php
│   │   └── verify.php
│   │
│   ├── farmer/
│   │   ├── dashboard.php
│   │   ├── add_product.php
│   │   └── view_bids.php
│   │
│   ├── buyer/
│   │   ├── dashboard.php
│   │   ├── market.php
│   │   └── checkout.php
│   │
│   ├── driver/
│   │   └── dashboard.php
│   │
│   ├── agronomist/
│   │   └── dashboard.php
│   │
│   └── admin/
│       └── dashboard.php
│
├── phpmailer/
│   ├── src/
│   │   ├── PHPMailer.php
│   │   ├── SMTP.php
│   │   └── Exception.php
│   └── composer.json
│
├── public/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   ├── images/
│   └── uploads/
│
├── index.php
├── .env                         # holds db + email credentials
└── composer.json
