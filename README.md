<div align="center">

# 🛒 WooCommerce Checkout Customization

**A modular, lightweight plugin to customize the WooCommerce checkout. Turn on only what you need.**

![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?style=flat&logo=wordpress&logoColor=white)
![WooCommerce](https://img.shields.io/badge/WooCommerce-7.0%2B-96588A?style=flat&logo=woocommerce&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat&logo=php&logoColor=white)
![HPOS](https://img.shields.io/badge/HPOS-compatible-success?style=flat)
![Version](https://img.shields.io/badge/version-1.0.0-blue?style=flat)
![License](https://img.shields.io/badge/license-GPL--2.0-orange?style=flat)

</div>

---

## ✨ Why this plugin?

Most stores need the same checkout changes: fewer fields, a valid phone number, a COD fee, a minimum order, a better thank-you message. Instead of pasting random snippets into `functions.php`, this plugin gives you **16 ready-made features in one place**, each with an on/off switch.

- 🔌 **Safe by default:** every feature is **off** until you enable it
- 🧩 **Modular:** clean, separate files for each area of the checkout
- ⚙️ **Configurable:** change amounts, text and fields with simple filters
- 🗄️ **HPOS compatible:** order data is saved with the WooCommerce CRUD API
- 🪶 **Lightweight:** no settings pages, no database tables, no extra CSS/JS files

---

## 📦 Features

| Feature key | What it does | Module |
|---|---|---|
| `remove_optional_fields` | Removes Company and Address line 2 | Checkout fields |
| `phone_validation` | Makes phone required and validates the format (Bangladesh mobile by default) | Checkout fields |
| `reorder_fields` | Name → Phone → Email → Address order | Checkout fields |
| `custom_labels` | Friendlier labels and placeholders | Checkout fields |
| `disable_order_notes` | Removes the "Order notes" field | Checkout fields |
| `custom_fields` | Adds delivery date + "How did you hear about us?"; shown in admin, emails, thank-you page & My Account | Custom fields |
| `place_order_text` | Changes the "Place order" button text | Checkout UX |
| `skip_cart` | Sends customers straight to checkout after "Add to cart" | Checkout UX |
| `trust_message` | Shows a secure-checkout message above the button | Checkout UX |
| `free_shipping_notice` | "Add X more for free shipping" notice | Checkout UX |
| `remove_coupon_form` | Hides the coupon form on checkout | Checkout UX |
| `cod_fee` | Adds a fee when Cash on Delivery is chosen | Fees & payments |
| `minimum_order` | Blocks orders below a minimum subtotal | Fees & payments |
| `hide_cod_above_amount` | Hides COD for large orders | Fees & payments |
| `custom_thankyou_text` | Personalized thank-you message with name and order number | Thank-you page |
| `thankyou_redirect` | Redirects to your own thank-you page (secure, key-checked) | Thank-you page |

---

## ⚠️ Important: classic vs block checkout

This plugin uses the **classic checkout** hooks. Since WooCommerce 8.3, new stores use the **Checkout block** by default, which ignores these hooks.

To use the classic checkout:
1. Go to **Pages → Checkout → Edit**.
2. Delete the Checkout block and add a **Shortcode** block with `[woocommerce_checkout]` (or use the "Classic Checkout" block).
3. Update the page.

Elementor users: the Elementor **Checkout** widget and most page builders use the classic checkout, so they work out of the box.

---

## 🚀 Installation

**Option A: as a plugin (recommended)**
1. Click **Code → Download ZIP** on this page.
2. In WordPress go to **Plugins → Add New → Upload Plugin** and upload the ZIP.
3. Activate **WooCommerce Checkout Customization**. Nothing changes yet, because every feature is off.

**Option B: copy single snippets**
Each file in `includes/` is readable on its own. Copy the block you need into a Code Snippets snippet and replace `wccc_enabled( '...' )` with `true`.

---

## ⚙️ Configuration

Turn features on with the `wccc_features` filter in your child theme's `functions.php`, a Code Snippets snippet, or an mu-plugin:

~~~php
add_filter( 'wccc_features', function ( $features ) {
	return array_merge( $features, array(
		'phone_validation' => true,
		'custom_fields'    => true,
		'cod_fee'          => true,
		'minimum_order'    => true,
	) );
} );

// Change the amounts (store currency).
add_filter( 'wccc_cod_fee', fn() => 60 );
add_filter( 'wccc_minimum_order_amount', fn() => 300 );
~~~

👉 A complete example with every option is in [`examples/enable-features.php`](examples/enable-features.php).

### Filters

| Filter | Default | Description |
|---|---|---|
| `wccc_features` | all `false` | Enable / disable features |
| `wccc_fields_to_remove` | Company, Address 2 | Fields removed by `remove_optional_fields` |
| `wccc_phone_pattern` | Bangladesh mobile regex | Phone validation pattern |
| `wccc_custom_fields` | Delivery date, referral source | Add, change or remove custom fields |
| `wccc_place_order_text` | "Confirm Order" | Checkout button text |
| `wccc_trust_message` | Secure checkout text | Message above the button (HTML allowed) |
| `wccc_free_shipping_threshold` | `1000` | Amount for the free-shipping notice |
| `wccc_cod_fee` / `wccc_cod_fee_label` | `50` / "Cash on Delivery fee" | COD fee amount and label |
| `wccc_minimum_order_amount` | `500` | Minimum order subtotal |
| `wccc_cod_max_amount` | `20000` | Hide COD above this subtotal |
| `wccc_thankyou_text` | Personalized text | Thank-you message |
| `wccc_thankyou_redirect_url` | empty (off) | Custom thank-you page URL on your site |

---

## 📁 Project structure

~~~
woocommerce-checkout-customization/
├── woocommerce-checkout-customization.php   # Plugin header, feature switches, loader
├── includes/
│   ├── checkout-fields.php                  # Remove, validate, reorder, relabel fields
│   ├── custom-fields.php                    # Extra fields + save & display
│   ├── checkout-ux.php                      # Button text, skip cart, notices
│   ├── fees-and-payments.php                # COD fee, minimum order, gateway rules
│   └── thank-you.php                        # Thank-you text & redirect
├── examples/
│   └── enable-features.php                  # Copy-paste configuration example
├── CHANGELOG.md
└── LICENSE
~~~

---

## 🛠️ Troubleshooting

- **Nothing changed after activating:** that's expected. Enable features with the `wccc_features` filter.
- **Changes don't show on checkout:** your store probably uses the Checkout block. See *classic vs block checkout* above.
- **COD fee doesn't update:** clear your cache plugin's cache and make sure the checkout page isn't cached.
- **Skip cart doesn't redirect on shop pages:** the plugin turns off AJAX add-to-cart automatically; clear the cache and try again.
- **Always test on a staging site** and keep a backup before changing a live store.

---

## 🗺️ Roadmap

- [ ] Checkout block (Store API) support
- [ ] Settings page in wp-admin
- [ ] District / Upazila dropdown for Bangladesh addresses
- [ ] Conditional fees by shipping zone

Ideas and pull requests are welcome!

---

## 🤝 Contributing

1. Fork the repository
2. Create a branch: `git checkout -b feature/my-feature`
3. Commit your changes and open a Pull Request

Please follow the [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/).

---

## 📄 License

[GPL-2.0-or-later](LICENSE), the same license as WordPress and WooCommerce.

---

<div align="center">

### 👨‍💻 Built by Md Nazmul Islam (Developer Naz)

Web Developer | WordPress & Shopify Expert | SEO Specialist

[![LinkedIn](https://img.shields.io/badge/LinkedIn-developernaz-0A66C2?style=flat&logo=linkedin&logoColor=white)](https://www.linkedin.com/in/developernaz/)
[![GitHub](https://img.shields.io/badge/GitHub-developer--naz-181717?style=flat&logo=github&logoColor=white)](https://github.com/developer-naz)

**Need a custom WooCommerce checkout for your store? Feel free to reach out.**

⭐ If this plugin helped you, please give the repo a star!

</div>
