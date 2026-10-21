# Bushido US theme

WordPress theme for us.bushidonepal.com, built from the "Bushido US Home" design.
Classic PHP theme with WooCommerce templates. The theme itself is in `bushido-us/`.

## Files

- `bushido-us.zip`: the theme, ready to upload in WordPress.
- `bushido-us-product-images.zip`: the six transparent product cut-outs from the design.
- `product-images/`: the same cut-outs, unzipped.

## Set up on WordPress

1. Plugins → Add New: install and activate **WooCommerce**. Skip or finish its setup wizard.
2. WooCommerce → Settings → General: Currency **US dollar**, Number of decimals **0** (shows `$45`, not `$45.00`).
3. Appearance → Themes → Add New → Upload Theme: upload `bushido-us.zip`, then **Activate**.
4. Pages → Add New: create **Archive**, **About** and **Contact**. A page with the slug `archive` uses the sold-out grid automatically.
5. Products: import them, then set each product's main image (see below).
6. Optional: Appearance → Menus, create a menu, add Home, Shop, Archive, About and Contact, and assign it to **Bottom navigation**. Until you do, the theme shows those links itself.

## How the pages work

| Page | Shows |
|---|---|
| Home | In-stock products, in the order set under Products → Sorting |
| Shop and categories | In-stock products |
| Archive (slug `archive`) | Sold-out products, greyed, labelled "Sold out" |
| About, Contact, any page | Page title and content |
| Product, Cart, Checkout, My account | WooCommerce's pages, restyled to match |

- To change the side figures: Appearance → Customize → Side figures.
- To change the logo: Appearance → Customize → Site Identity → Logo.

## Importing products from bushidonepal.com

1. On the main site: Products → Export → CSV.
2. On the US site: Products → Import → upload the CSV.
3. Prices: the main site uses "price based on countries", so the exported prices may be the base (NPR) ones. Check each product and enter its USD price.
4. Images: the grid is designed for transparent cut-outs. Edit each product, set the cut-out from `bushido-us-product-images.zip` as its **Product image**, and move the old photo into the Product gallery.

## Local test

`_test/` (git-ignored) holds a WordPress Playground blueprint with WooCommerce and sample products:

```
cd _test
npx @wp-playground/cli@3.1.60 server --port=9400 \
  --mount=../bushido-us:/wordpress/wp-content/themes/bushido-us \
  --mount=./seed:/seed --blueprint=./blueprint.json
```
