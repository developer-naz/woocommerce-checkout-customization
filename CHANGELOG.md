# Changelog

All notable changes to this project are documented in this file.
The format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and the project uses [Semantic Versioning](https://semver.org/).

## [1.0.0] - 2026-11-12

### Added
- Modular plugin structure with feature switches (all off by default) via the `wccc_features` filter.
- **Checkout fields:** remove optional fields, phone validation (Bangladesh mobile by default), field reordering, friendlier labels, option to disable order notes.
- **Custom fields:** preferred delivery date and "How did you hear about us?", saved to the order and shown in wp-admin, emails, thank-you page and My Account.
- **Checkout UX:** custom "Place order" text, skip cart, trust message, free-shipping progress notice, option to remove the coupon form.
- **Fees & payments:** Cash on Delivery fee, minimum order amount, hide COD above a set amount.
- **Thank-you page:** personalized message and secure redirect to a custom page.
- WooCommerce HPOS compatibility declaration.
- Usage example in `examples/enable-features.php`.
