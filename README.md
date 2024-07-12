
# Laravel Marketplaces

This Laravel responsive web application allows users to list items for sale and purchase items from other users.

Technologies: Laravel, React, Inertia.js, React Bootstrap, MySQL, Sass

Application uses [standard Laravel project structure](https://laravel.com/docs/master/structure) with React components in the [resources/js](resources/js) directory and Scss styles in the [resources/scss](resources/scss) directory.

## Development setup

Setup with Docker and [Laravel Sail](https://laravel.com/docs/master/sail).

### Prerequisites

- [Git](https://git-scm.com/downloads) (for cloning the repository)
- [Docker](https://www.docker.com/get-started)

### Setup

- Clone the repository
```bash
git clone https://github.com/lukasondrejka/laravel-marketplace.git
```

- Change into the project directory
```bash
cd laravel-marketplace
```

- Start the application using Laravel Sail
```bash
./vendor/bin/sail up
```

- Run the database migrations
```bash
./vendor/bin/sail artisan migrate
```

- Seed categories
```bash
./vendor/bin/sail artisan db:seed --class=CategorySeeder
```

- Install the npm dependencies
```bash
./vendor/bin/sail npm install
```

- Start React development server
```bash
./vendor/bin/sail npm run dev
```

- Visit the application in your browser at [http://localhost](http://localhost)

## Resources

- [Laravel](LARAVEL.md)
- [Laravel Documentation](https://laravel.com/docs)
- [Inertia.js](https://inertiajs.com)
- [React](https://reactjs.org)
- [React Bootstrap](https://react-bootstrap.github.io/)
