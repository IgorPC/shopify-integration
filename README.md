# Shopify Integration

## Summary
This application is a mono repo with specific containers for DB, Api, and Web. The backend made with Laravel 12 and PHP 8.2, and the frontend made with Vue 3 and TypeScript.
We have a docker to help when you try to build this application, the docker will build 3 containers, one for the database (shopify_db),
one for the api (shopify_api) and another for the web application (shopify_web). The project uses a Schema-first approach with Laravel Lighthouse.

## Technologies:
- PHP 8.2
- Laravel 12
- Laravel LightHouse (For GraphQL)
- MySQL (For local data, Jobs, and Queues)
- TypeScript
- Vue 3
- Vite
- apollo
- Bootstrap

## Quick Start

1. **Clone:**
   ```bash
   cp api/.env.example api/.env

2. **config:**
   ```bash
   cp api/.env.example api/.env
   
- ! IMPORTANT ! Do not forget to copy the /api.env.example and paste as .env

## Requirements before building:
- Docker installed and working
- Account on Shopify Partners
- A basic development store with a custom app
- Setup admin API scopes (write_products, read_products, read_locations, write_inventory, read_inventory)
- Get your API ACCESS TOKEN and Shop URL
- Paste your SHOP URL on api/.env on SHOPIFY_SHOP_URL (URL example: dev-store-749237498237499095.myshopify.com)
- Paste your API ACCESS TOKEN on api/.env on SHOPIFY_API_ACCESS_TOKEN (TOKEN example: shpat_361b...)

## Building the application:
- Access the project root folder via terminal
- Execute docker-compose up (or docker-compose up -d but make sure to check the container logs)
- Wait until all containers built and make sure to check the api container log until the message "----> API successfully built !" displays, then all services will be ready

## Accessing the App:
- Frontend: http://localhost:5173
- Backend API: http://localhost:8000

## Useful Commands inside shopify_api container:
- Run migrations: docker exec -it shopify_api php artisan migrate
- Clear Cache: docker exec -it shopify_api php artisan optimize

## Implemented Features:
- List all products in your store with pagination
- Create a product in your store (title, description, price, inventory)
- Update a product in your store (title, description, price)
- Delete a product from your store
- Sync all local products with your products on Shopify Store
- Sync a single product with your Shopify Store
- List missing products from your shopify store that exist only locally
- System log for audit

## Improvements that I was not able to complete in time:
- Implement tests on the web app (frontend)
- Implement storage update for a product
- Implement feature to add and manage images for a product
- Filters for Product and System Log
- Improve feedback when an action is done and we go back to the Listing.
- Implement webhooks using the api routes from Laravel

## Known issues and bugs:
- After updating an item, quantity is always updated to zero on Shopify and because I sync after every update, it's also zero in the local Database
- After creating a product, if you stay in the modal and create a new one, the success message does not display
- There's no validation to prevent duplicated products with the same title