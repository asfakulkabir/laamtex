# WooCommerce Variable Product: Full Structure Spec (for Laravel build)

Goal: rebuild the same product + attributes + variations system in a Laravel e-commerce admin panel, matching how WooCommerce works.

---

## 1. Core idea

- **Product (parent)** holds shared info: name, description, category, gallery, and the list of attributes.
- **Attribute** = an option type (Color, Size).
- **Attribute value (term)** = one choice (Red, Blue, S, M, L).
- **Variation** = one exact combination (Red + M). This is the real sellable item. It has its own SKU, price, stock, weight, and image.
- Product types: `simple`, `variable` (add `grouped` and `external` only if needed).

---

## 2. Parent product fields

| Field | Type | Notes |
|---|---|---|
| id | bigint | |
| type | enum: simple, variable | |
| name | string | |
| slug | string, unique | |
| status | enum: draft, pending, private, publish | |
| featured | bool | |
| catalog_visibility | enum: visible, catalog, search, hidden | |
| description | longtext | |
| short_description | text | |
| sku | string, nullable, unique | Parent SKU. Optional for variable products |
| regular_price, sale_price | decimal | Used only for simple products. For variable, price comes from variations |
| date_on_sale_from, date_on_sale_to | datetime, nullable | Scheduled sale |
| tax_status | enum: taxable, shipping, none | |
| tax_class | string | |
| manage_stock | bool | If true at parent level, all variations share this stock |
| stock_quantity | int, nullable | |
| stock_status | enum: instock, outofstock, onbackorder | |
| backorders | enum: no, notify, yes | |
| low_stock_amount | int, nullable | |
| sold_individually | bool | |
| weight | decimal | |
| length, width, height | decimal | |
| shipping_class_id | fk, nullable | |
| virtual | bool | |
| downloadable | bool | |
| image_id | fk to media | Main image |
| gallery | many media, ordered | Gallery images (shared by all variations) |
| categories | many-to-many | |
| tags | many-to-many | |
| menu_order | int | |
| default_attributes | array | Pre-selected options on the product page |
| min_price, max_price | decimal | **Cached** from active variations, used for listing pages |
| total_sales | int | |
| created_at, updated_at | timestamps | |

---

## 3. Attribute tables

### attributes (global, reusable)
| Field | Type |
|---|---|
| id | bigint |
| name | string (Color) |
| slug | string, unique (color) |
| type | enum: select, color, image, button (for swatches) |
| order_by | enum: custom, name, id |

### attribute_values (called "terms" in WooCommerce)
| Field | Type |
|---|---|
| id | bigint |
| attribute_id | fk |
| name | string (Red) |
| slug | string (red) |
| color_code | string, nullable (#ff0000) |
| image | string, nullable |
| sort_order | int |

### product_attributes (which attributes a product uses)
This is the row where the admin ticks **Visible on product page** and **Used for variations**.

| Field | Type | Notes |
|---|---|---|
| id | bigint | |
| product_id | fk | |
| attribute_id | fk, nullable | Null means custom attribute (only for this product) |
| custom_name | string, nullable | Used when attribute_id is null |
| custom_options | json, nullable | Used when attribute_id is null, e.g. ["Cotton","Silk"] |
| position | int | Order shown |
| is_visible | bool | Show in "Additional information" tab |
| is_variation | bool | Used to make variations |

### product_attribute_values (selected values for a product)
| Field | Type |
|---|---|
| product_id | fk |
| attribute_id | fk |
| attribute_value_id | fk |
| unique(product_id, attribute_value_id) | |

Rule: only attributes with `is_variation = true` create variations. Other attributes (like Material) are info only.

---

## 4. Variation table: product_variations

| Field | Type | Notes |
|---|---|---|
| id | bigint | |
| product_id | fk, cascade delete | Parent |
| sku | string, nullable, unique | |
| combo_key | string | Sorted value IDs joined by "-", e.g. "2-7". Unique per product |
| status | enum: publish, private | `private` = disabled (hidden in shop) |
| description | text, nullable | Shown on product page when this variation is picked |
| regular_price | decimal, nullable | |
| sale_price | decimal, nullable | |
| date_on_sale_from, date_on_sale_to | datetime, nullable | |
| tax_status | enum: taxable, shipping, none, or "parent" | |
| tax_class | string, nullable ("parent") | |
| manage_stock | bool or "parent" | |
| stock_quantity | int, nullable | |
| stock_status | enum: instock, outofstock, onbackorder | |
| backorders | enum: no, notify, yes, or "parent" | |
| low_stock_amount | int, nullable | |
| weight | decimal, nullable | |
| length, width, height | decimal, nullable | |
| shipping_class_id | fk, nullable | |
| virtual, downloadable | bool | |
| image_id | fk to media, nullable | **One image per variation** (WooCommerce default) |
| menu_order | int | Order in admin list and dropdown |
| created_at, updated_at | timestamps | |

Unique index: `(product_id, combo_key)`.

### variation_attribute_values (what makes each variation)
| Field | Type | Notes |
|---|---|---|
| variation_id | fk, cascade | |
| attribute_id | fk, nullable | Null for custom attribute |
| custom_name | string, nullable | For custom attribute |
| attribute_value_id | fk, nullable | **Null = "Any value"** |
| custom_value | string, nullable | For custom attribute |
| unique(variation_id, attribute_id) | | One value per attribute |

### variation_images (optional: gallery per variation)
WooCommerce core does NOT have this. Plugins add it. Build it only if needed.

| Field | Type |
|---|---|
| variation_id | fk |
| media_id | fk |
| sort_order | int |

---

## 5. Inheritance rules (very important)

If a variation field is empty (null) or set to "parent", use the parent product value. This applies to:
- tax_status, tax_class
- manage_stock, backorders
- weight, dimensions
- shipping_class
- sku (does NOT inherit; variation SKU must be its own or empty)
- image: if empty, show the parent's main image

Stock rule:
- If parent `manage_stock = true`, stock is tracked on the parent and shared by all variations.
- If parent `manage_stock = false` and variation `manage_stock = true`, each variation has its own stock.
- Never reduce stock on both.

---

## 6. Price rules

- Variable parent has no price of its own.
- Cache `min_price` and `max_price` on the parent from **active (publish) and in-stock or purchasable** variations. Recalculate on every variation save/delete.
- Active price of a variation = `sale_price` if the sale is active (date range valid), else `regular_price`.
- Listing page: if min = max show "৳500", else show "৳500 – ৳900".
- Product page: show the range until the customer picks a full combination, then show that variation's price (with regular price struck through if on sale).

---

## 7. "Any" value rule

If a variation's attribute value is null, it means **any value**. Example: variation "Color = Red, Size = Any" matches Red + S, Red + M, Red + L. Exact-match variations should win over "Any" ones. Keep this optional; a simpler build can require every variation to have all values set.

---

## 8. Admin panel UI (same as WooCommerce)

### Product edit page
1. Product type dropdown: Simple / Variable.
2. Tabs (left side of the "Product data" box): General, Inventory, Shipping, Linked products, **Attributes**, **Variations**, Advanced.

### Attributes tab
- Dropdown "Add existing attribute" (global attributes) + button "Add new" (custom attribute).
- For each attribute row:
  - Name
  - Values (multi-select for global, pipe-separated text for custom)
  - Buttons: Select all, Select none, Add new value
  - Checkbox: **Visible on the product page**
  - Checkbox: **Used for variations**
  - Remove button, drag handle to reorder
- Button: **Save attributes**

### Variations tab
- Top toolbar:
  - **Generate variations** (creates all combinations from the "Used for variations" attributes; skips ones that already exist)
  - **Add manually** (one blank variation)
  - Bulk actions dropdown: Set regular prices, Increase/decrease prices (fixed or %), Set sale prices, Set stock qty, Toggle "Manage stock", Set weight/dimensions, Set downloadable, Delete all variations
  - **Default form values** (default selected options): one dropdown per attribute, "No default" or a value
- Each variation is a collapsible row showing:
  - ID and the attribute dropdowns (Color, Size), each with "Any Color" as the empty option
  - Image thumbnail (click to choose or remove)
  - Checkboxes: **Enabled**, **Downloadable**, **Virtual**, **Manage stock?**
  - SKU
  - Regular price, Sale price, Sale schedule dates
  - Stock quantity, Allow backorders, Low stock threshold (shown only if Manage stock is on)
  - Stock status (shown if Manage stock is off)
  - Weight, Length, Width, Height
  - Shipping class ("Same as parent")
  - Tax class ("Same as parent")
  - Description
  - Remove button, drag handle
- Pagination (default 15 variations per page).
- Button: **Save changes**

### Validation
- Duplicate combination is not allowed (unique `combo_key`).
- SKU must be unique across all products and variations.
- Sale price must be less than regular price.
- Variation cannot be saved without at least one attribute value (unless it is fully "Any").

---

## 9. Storefront behavior

- Show one dropdown (or swatch/button) per attribute that is used for variations.
- Load all variation data as JSON on the page (id, attributes, price, sale price, stock status, stock qty, image, sku, weight, dimensions, description, is_purchasable). If the product has more than about 30 variations, load through AJAX instead.
- When the customer picks a value:
  - Filter which other values are still available (disable or grey out options that have no matching variation).
  - When all attributes are picked, find the matching variation (exact match first, then "Any").
  - Update: price, stock text ("In stock" / "Only 3 left" / "Out of stock"), SKU, weight/dimensions, description, and **the main image** (swap to the variation image; if none, keep the parent image).
  - Enable the Add to cart button.
- Show a "Clear" link to reset all selections.
- Apply default selections from `default_attributes` on page load.
- Add to cart must send `product_id`, `variation_id`, and the chosen attribute values.

---

## 10. Cart and order rules

- Cart item key = product_id + variation_id + attributes.
- Cart line stores: product_id, variation_id, quantity, chosen attributes.
- Price and stock checks use the **variation**.
- Order item stores: product_id, variation_id, name, variation label ("Red / M"), sku, unit price, qty, tax, total, and a snapshot of attributes. Never depend on live product data for old orders.
- Reduce stock on order (or payment, as your store decides). Restore stock on cancel or refund.
- Do not hard-delete a variation that has orders. Set it to `private` or soft delete.

---

## 11. Example JSON (same shape as the WooCommerce REST API)

### Parent product
```json
{
  "id": 123,
  "name": "Classic T-Shirt",
  "slug": "classic-t-shirt",
  "type": "variable",
  "status": "publish",
  "featured": false,
  "catalog_visibility": "visible",
  "description": "<p>Full description</p>",
  "short_description": "<p>Short text</p>",
  "sku": "TSHIRT",
  "price": "500",
  "regular_price": "",
  "sale_price": "",
  "on_sale": true,
  "purchasable": true,
  "tax_status": "taxable",
  "tax_class": "",
  "manage_stock": false,
  "stock_quantity": null,
  "stock_status": "instock",
  "backorders": "no",
  "sold_individually": false,
  "weight": "0.3",
  "dimensions": { "length": "30", "width": "25", "height": "2" },
  "shipping_class": "",
  "categories": [{ "id": 15, "name": "Clothing", "slug": "clothing" }],
  "tags": [],
  "images": [
    { "id": 901, "src": "https://site.com/uploads/tshirt-main.jpg", "alt": "" },
    { "id": 902, "src": "https://site.com/uploads/tshirt-back.jpg", "alt": "" }
  ],
  "attributes": [
    {
      "id": 1, "name": "Color", "position": 0,
      "visible": true, "variation": true,
      "options": ["Red", "Blue"]
    },
    {
      "id": 2, "name": "Size", "position": 1,
      "visible": true, "variation": true,
      "options": ["S", "M", "L"]
    },
    {
      "id": 0, "name": "Material", "position": 2,
      "visible": true, "variation": false,
      "options": ["Cotton"]
    }
  ],
  "default_attributes": [
    { "id": 1, "name": "Color", "option": "Red" }
  ],
  "variations": [201, 202, 203, 204, 205, 206],
  "menu_order": 0
}
```

### One variation
```json
{
  "id": 201,
  "sku": "TSHIRT-RED-S",
  "status": "publish",
  "description": "",
  "regular_price": "600",
  "sale_price": "500",
  "price": "500",
  "on_sale": true,
  "date_on_sale_from": null,
  "date_on_sale_to": null,
  "virtual": false,
  "downloadable": false,
  "tax_status": "taxable",
  "tax_class": "parent",
  "manage_stock": true,
  "stock_quantity": 25,
  "stock_status": "instock",
  "backorders": "no",
  "low_stock_amount": 3,
  "weight": "0.3",
  "dimensions": { "length": "", "width": "", "height": "" },
  "shipping_class": "",
  "image": { "id": 950, "src": "https://site.com/uploads/tshirt-red.jpg", "alt": "Red" },
  "attributes": [
    { "id": 1, "name": "Color", "option": "Red" },
    { "id": 2, "name": "Size", "option": "S" }
  ],
  "menu_order": 0
}
```

---

## 12. API endpoints to build (optional, for a Vue/React admin)

- `GET/POST /products`, `GET/PUT/DELETE /products/{id}`
- `GET/POST /attributes`, `GET/PUT/DELETE /attributes/{id}`
- `GET/POST /attributes/{id}/values`, `PUT/DELETE /attributes/{id}/values/{valueId}`
- `GET/POST /products/{id}/variations`, `GET/PUT/DELETE /products/{id}/variations/{variationId}`
- `POST /products/{id}/variations/generate`
- `POST /products/{id}/variations/batch` (bulk create, update, delete)

---

## 13. Build order for the agent

1. Migrations: attributes, attribute_values, products (add type and fields), product_attributes, product_attribute_values, product_variations, variation_attribute_values, optional variation_images.
2. Models with relations and the inheritance helpers (price, stock, weight, image fall back to parent).
3. Attribute and value CRUD in admin.
4. Product form: type switch, Attributes tab, Save attributes.
5. Generate variations (cartesian product, use `combo_key`, safe to run again, no duplicates).
6. Variations tab: editable rows, image upload, bulk actions, pagination, save.
7. Recalculate parent `min_price`, `max_price`, and stock status after every variation change.
8. Storefront: JSON variation data, dropdown logic, image swap, price update, availability filtering.
9. Cart: store `variation_id`. Checkout: order item snapshot. Stock reduction on the variation.
10. Tests and edge cases (see below).

## 14. Edge cases to test

- Generate twice: no duplicate variations.
- Remove an attribute value from the product: its variations become disabled or are deleted (ask before deleting).
- Variation with no image: parent image is shown.
- All variations out of stock: parent shows "Out of stock".
- Sale date ends: price falls back to regular price and the cached min/max is refreshed.
- Variation with orders is never hard-deleted.
- A product with 100+ variations still loads fast (AJAX loading, pagination in admin).
- Changing an attribute name or value does not break old orders (snapshot).
