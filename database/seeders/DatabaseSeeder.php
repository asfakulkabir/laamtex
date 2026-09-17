<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\DeliveryCharge;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Admin User
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@outfitt.com',
            'password' => Hash::make('adminpassword'),
            'is_admin' => true,
        ]);

        // Seed a regular test user as well
        User::create([
            'name' => 'Customer',
            'email' => 'customer@outfitt.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        // 2. Seed Delivery Charges
        DeliveryCharge::create([
            'zone' => 'Inside Dhaka',
            'charge' => 60.00,
            'estimated_days' => '2-3 Days',
        ]);

        DeliveryCharge::create([
            'zone' => 'Outside Dhaka',
            'charge' => 120.00,
            'estimated_days' => '3-5 Days',
        ]);

        // 3. Seed Categories
        $women = Category::create([
            'name' => 'Women',
            'group_name' => 'Gender',
            'slug' => 'women',
        ]);

        $men = Category::create([
            'name' => 'Men',
            'group_name' => 'Gender',
            'slug' => 'men',
        ]);

        $accessories = Category::create([
            'name' => 'Accessories',
            'group_name' => 'Department',
            'slug' => 'accessories',
        ]);

        $dresses = Category::create([
            'name' => 'Dresses',
            'parent_id' => $women->id,
            'group_name' => 'Clothing',
            'slug' => 'dresses',
        ]);

        $shirts = Category::create([
            'name' => 'Shirts',
            'parent_id' => $men->id,
            'group_name' => 'Clothing',
            'slug' => 'shirts',
        ]);

        // 4. Seed Products
        // Product 1: Variable Casual Summer Dress
        $product1 = Product::create([
            'user_id' => $admin->id,
            'name' => 'Casual Summer Dress',
            'slug' => 'casual-summer-dress',
            'short_description' => 'A beautiful, lightweight casual summer dress with vibrant patterns, perfect for hot sunny days.',
            'description' => 'Stay cool and stylish this summer with our Casual Summer Dress. Featuring a soft and breathable fabric blend, a flattering tiered design, and gorgeous pink/purple floral motifs. Complete with ruffle details at the sleeves and a tie-waist to accent your silhouette.',
            'product_type' => 'variable',
            'is_active' => true,
            'is_featured' => true,
            'seo_title' => 'Casual Summer Dress - Pink & Purple Floral Dress | laamtex',
            'meta_description' => 'Buy Casual Summer Dress at laamtex. Soft, breathable pink and purple tiered floral dress with flutter sleeves, perfect for summer outings.',
        ]);
        $product1->categories()->sync([$women->id, $dresses->id]);

        ProductImage::create([
            'product_id' => $product1->id,
            'image' => 'product_images/pink_dress.png',
            'name' => 'front_view',
            'alt_text' => 'Casual Summer Dress front view',
            'is_featured' => true,
            'order' => 0,
        ]);

        ProductVariation::create([
            'product_id' => $product1->id,
            'size' => 'S',
            'color' => 'Pink',
            'price' => 29.99,
            'stock' => 5,
        ]);
        ProductVariation::create([
            'product_id' => $product1->id,
            'size' => 'M',
            'color' => 'Pink',
            'price' => 32.99,
            'stock' => 10,
        ]);
        ProductVariation::create([
            'product_id' => $product1->id,
            'size' => 'L',
            'color' => 'Pink',
            'price' => 34.99,
            'stock' => 8,
        ]);
        ProductVariation::create([
            'product_id' => $product1->id,
            'size' => 'S',
            'color' => 'Purple',
            'price' => 29.99,
            'stock' => 4,
        ]);
        ProductVariation::create([
            'product_id' => $product1->id,
            'size' => 'M',
            'color' => 'Purple',
            'price' => 32.99,
            'stock' => 7,
        ]);
        ProductVariation::create([
            'product_id' => $product1->id,
            'size' => 'L',
            'color' => 'Purple',
            'price' => 34.99,
            'stock' => 6,
        ]);
        $product1->updateStockFromVariations();

        // Product 2: Variable Men's Shirt
        $product2 = Product::create([
            'user_id' => $admin->id,
            'name' => "Classic Men's Shirt",
            'slug' => 'classic-mens-shirt',
            'short_description' => 'A clean and timeless crisp white button-down shirt for formal or semi-formal occasions.',
            'description' => 'The cornerstone of any gentleman\'s wardrobe. Crafted from 100% premium long-staple cotton, this shirt offers unparalleled comfort, clean tailoring, and durability. Resists wrinkles for a sharp appearance throughout the day.',
            'product_type' => 'variable',
            'is_active' => true,
            'is_featured' => false,
            'seo_title' => "Classic Men's White Shirt | laamtex",
            'meta_description' => 'Shop our Classic Men\'s Shirt at laamtex. 100% premium cotton white button-down, sharp tailoring and wrinkle resistant.',
        ]);
        $product2->categories()->sync([$men->id, $shirts->id]);

        ProductImage::create([
            'product_id' => $product2->id,
            'image' => 'product_images/men_shirt.png',
            'name' => 'front_view',
            'alt_text' => "Classic Men's White Shirt front view",
            'is_featured' => true,
            'order' => 0,
        ]);

        ProductVariation::create([
            'product_id' => $product2->id,
            'size' => 'M',
            'color' => 'White',
            'price' => 24.99,
            'stock' => 15,
        ]);
        ProductVariation::create([
            'product_id' => $product2->id,
            'size' => 'L',
            'color' => 'White',
            'price' => 24.99,
            'stock' => 20,
        ]);
        ProductVariation::create([
            'product_id' => $product2->id,
            'size' => 'XL',
            'color' => 'White',
            'price' => 26.99,
            'stock' => 10,
        ]);
        $product2->updateStockFromVariations();

        // Product 3: Simple Purple Leather Handbag
        $product3 = Product::create([
            'user_id' => $admin->id,
            'name' => 'Purple Leather Handbag',
            'slug' => 'purple-leather-handbag',
            'short_description' => 'A luxury saffiano leather handbag in a deep regal purple, complete with gold-tone hardware.',
            'description' => 'Carry your essentials in style with our signature Purple Leather Handbag. Expertly crafted from scratch-resistant saffiano leather, featuring a spacious main compartment, internal zip pockets, an adjustable crossbody strap, and protective metal feet. Elevate any outfit instantly.',
            'product_type' => 'simple',
            'regular_price' => 49.99,
            'sale_price' => 39.99,
            'stock_quantity' => 15,
            'is_active' => true,
            'is_featured' => true,
            'seo_title' => 'Purple Saffiano Leather Handbag - Gold Hardware | laamtex',
            'meta_description' => 'Buy Purple Leather Handbag at laamtex. Premium scratch-resistant saffiano leather handbag with gold-tone hardware and adjustable strap.',
        ]);
        $product3->categories()->sync([$accessories->id]);

        ProductImage::create([
            'product_id' => $product3->id,
            'image' => 'product_images/leather_handbag.png',
            'name' => 'front_view',
            'alt_text' => 'Purple Saffiano Leather Handbag',
            'is_featured' => true,
            'order' => 0,
        ]);

        // Product 4: Simple Pink Silk Scarf
        $product4 = Product::create([
            'user_id' => $admin->id,
            'name' => 'Pink Silk Scarf',
            'slug' => 'pink-silk-scarf',
            'short_description' => 'A lightweight and elegant 100% mulberry silk scarf in pastel pink.',
            'description' => 'Add a delicate touch of luxury to your winter or spring look. Made from ultra-soft mulberry silk, this scarf flows beautifully and adds subtle color to dresses, blouses, or coats. Features finished hand-rolled edges.',
            'product_type' => 'simple',
            'regular_price' => 19.99,
            'sale_price' => null,
            'stock_quantity' => 25,
            'is_active' => true,
            'is_featured' => false,
            'seo_title' => 'Pink Mulberry Silk Scarf - Hand Rolled Edges | laamtex',
            'meta_description' => 'Shop Pink Silk Scarf at laamtex. 100% mulberry silk scarf, lightweight, soft, pastel pink.',
        ]);
        $product4->categories()->sync([$accessories->id]);
    }
}

