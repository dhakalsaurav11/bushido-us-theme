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
4. Pages → Add New: create **Story**, **Archive**, **About** and **Contact**. A page with the slug `archive` uses the sold-out grid automatically.
5. Products: import them, then set each product's main image (see below).
6. Optional: Appearance → Menus, create a menu, add Home, Story, Archive, About and Contact, and assign it to **Bottom navigation**. Until you do, the theme shows those links itself.

## How the pages work

| Page | Shows |
|---|---|
| Home | The shop: in-stock products, in the order set under Products → Sorting |
| Product | Opens as a dialog over Home: gallery with small thumbnails, `<` `>` arrows, sizes as text (several at once), Add to bag. A product's URL opens Home with its dialog showing |
| Story (slug `story`) | Larger logo with no rule under it, which grows on the way in and shrinks on the way out (Chrome, Edge, Safari). The side figures keep exactly their usual size and position but aren't cropped at the panel edge, so the rest of each figure shows in front of the centre column. The text runs down the middle, one section per Heading 2. An empty page shows the text from bushidonepal.com/about-us |
| Contact (slug `contact`) | Same layout as Story. An empty page shows "All sales are final.", the email address and Instagram. To edit, paste `contact-content.html` into the page's Code editor |
| Archive (slug `archive`) | Sold-out products, greyed, labelled "Sold out" |
| About, any other page | Page title and content |
| Cart | Story layout: large logo, side figures shown whole, the cart down the middle. An empty cart shows only its message and the link home (no "New in store") |
| Checkout, My account | WooCommerce's pages, restyled to match |

- WooCommerce's Shop page stays (WooCommerce needs it) but redirects to Home. Leave it out of the menu.
- Picking several sizes adds one of each to the bag; quantities can be changed in the cart.

- To change the side figures: Appearance → Customize → Side figures.
- To change the logo: Appearance → Customize → Site Identity → Logo.

## Importing products from bushidonepal.com

1. On the main site: Products → Export → CSV.
2. On the US site: Products → Import → upload the CSV.
3. Prices: the main store's currency is already USD, but several products have Nepali-rupee amounts typed in as dollars. Fix them on each **variation** (Product data → Variations), not just the parent product. As of Oct 2026 these were:
   - Dragon Hooded Sleeveless Zip-up Vest: $2,800
   - Ryujin Long Sleeves, Olive Green and Black: $2,500
   - Twin Dragon double layered tees: $1,999
   - The Flow State Tees: $1,500
   - Embroidered Sakura Pants: $3,500
   - Year of the Dragon Kimono: $2,500

   The rest ($35 jerseys, $50 hoodies and so on) look like real USD prices. Nearly every product is a variable product with a Size option, and the theme handles that.
4. Images: the grid is designed for transparent cut-outs. Edit each product, set the cut-out from `bushido-us-product-images.zip` as its **Product image**, and move the old photo into the Product gallery.

## Local test

`_test/` (git-ignored) holds a WordPress Playground blueprint with WooCommerce and sample products:

```
cd _test
npx @wp-playground/cli@3.1.60 server --port=9400 \
  --mount=../bushido-us:/wordpress/wp-content/themes/bushido-us \
  --mount=./seed:/seed --blueprint=./blueprint.json
```
