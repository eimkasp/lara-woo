# LaraWoo - WooCommerce Data Synchronization for Laravel

Migrate, sync and manage multiple WooCommerce stores in one Laravel application.

This command is designed to synchronize data between a WooCommerce store and a Laravel application. It is part of the console command suite and can be executed via the command line.

## Usage

To run the synchronization process, use the following command:
`php artisan sync:woocommerce`

## Queue

The sync process is queued using Laravel Jobs. This allows for asynchronous processing of the sync data.

You can also specify a particular channel to sync by using the `--channel` option followed by the channel ID:
`php artisan sync:woocommerce --channel=1`

To run the sync process with infinite memory and timeout limit, use the following command:
`php artisan queue:listen --memory=-1 --timeout=0` 



## Features

- Syncs products, customers, and orders from WooCommerce to the Laravel application.
- Handles product variations and images.
- Updates or creates new entries in the database based on the WooCommerce data.
- Utilizes Laravel jobs for asynchronous data processing.
- Logs the synchronization status and time for each channel.

## Meta Model

The `Meta` model is used to store additional information for various models in a polymorphic relationship. This allows for flexible addition of meta data to models such as `Product`, `Order`, and `Customer`.

### Meta Table Structure

The `meta` table consists of the following columns:
- `id` - The primary key.
- `metable_type` - The related model type.
- `metable_id` - The related model ID.
- `key` - The meta key.
- `value` - The meta value.

### Relationships

The `Meta` model defines a polymorphic relationship method `metable()` which allows it to be associated with multiple models.

## Customer Model

The `Customer` model represents the customers of the WooCommerce store. It includes fields such as the WooCommerce ID, first name, last name, email, and associated channel.

### Relationships

- `orders()` - A one-to-many relationship with the `Order` model.
- `channel()` - A many-to-one relationship with the `Channel` model.

## Product Resource

The `ProductResource` class in Filament provides an admin interface for managing products. It includes forms for creating and editing products, as well as a table for listing products with features such as sorting and searching.

### Features

- Forms for managing product details, variations, and images.
- Table columns for product attributes and meta data.
- Image preview in the product list.
- Dynamic form fields based on the product's meta data.

## Order Resource

The `OrderResource` class in Filament provides an admin interface for managing orders. It includes forms for creating and editing orders, as well as a table for listing orders with features such as sorting and searching.

### Features

- Forms for managing order details, products, and meta data.
- Table columns for order attributes and customer information.
- Badge on the navigation menu showing the total number of orders.

## Running the sync

To initiate the synchronization process, run the following command in your terminal:
`php artisan sync:woocommerce`

For more detailed output during the synchronization, you can run the command in verbose mode:
`php artisan sync:woocommerce -v`




Running the sync
`php artisan sync:woocommerce`