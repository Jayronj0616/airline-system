# Airline Revenue & Dynamic Pricing System

A full-featured airline booking and revenue management system with dynamic pricing algorithms, real-time inventory control, and demand-based revenue optimization—similar to real airline backend systems.

## 🎯 Key Features

- **Dynamic Pricing Engine** - Prices adjust based on demand, inventory, and time to departure
- **Real-time Seat Management** - Concurrency-safe booking with pessimistic locking
- **Booking Flow** - Search → Hold (15 min) → Payment → Confirmation
- **Admin Dashboard** - Revenue analytics, demand monitoring, and pricing controls
- **Demand Simulation** - Background jobs that simulate realistic booking patterns
- **Overbooking Management** - Controlled overbooking with safety rules
- **Fare Rules Engine** - Configurable refund/change policies per fare class
- **User Profiles** - Booking history, favorite routes, and saved payment methods
- **Dark Mode** - Full dark mode support throughout the application
- **Add-ons** - Meals, baggage, seat selection, and priority boarding

## 📋 Requirements

- **PHP** >= 8.1
- **Composer** >= 2.0
- **Node.js** >= 16.x & npm
- **MySQL** >= 8.0
- **Web Server** (Apache/Nginx) or Laravel's built-in server

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Jayronj0616/airline-system.git
cd airline-system
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file:

```bash
cp .env.example .env
```

Generate application key:

```bash
php artisan key:generate
```

Configure your database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=airline_system
DB_USERNAME=root
DB_PASSWORD=
```

Configure queue connection (required for background jobs):

```env
QUEUE_CONNECTION=database
```

### 5. Create Database

Create a MySQL database named `airline_system`:

```sql
CREATE DATABASE airline_system;
```

### 6. Run Migrations

```bash
php artisan migrate
```

### 7. Seed Database

This will create:
- Admin user (admin@airline.com / admin123)
- Regular user (user@airline.com / user123)
- Aircraft types
- Fare classes (Economy, Business, First)
- Fare rules
- Sample flights

```bash
php artisan db:seed
```

### 8. Build Frontend Assets

For development:
```bash
npm run dev
```

For production:
```bash
npm run build
```

## 🏃 Running the Application

### Start the Development Server

```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

### Start the Queue Worker (Required)

The system uses background jobs for critical operations. Run this in a separate terminal:

```bash
php artisan queue:work
```

### Start the Task Scheduler (Required)

The scheduler handles automated tasks like releasing expired holds and updating prices. Run this in a separate terminal:

```bash
php artisan schedule:work
```

Alternatively, you can set up a cron job (for production):

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

## 📱 Using the System

### For Passengers

1. **Search Flights** - Go to homepage and search for flights by origin, destination, and date
2. **Select Fare Class** - Choose from Economy, Business, or First Class
3. **Book Flight** - Enter passenger details (seats are held for 15 minutes)
4. **Payment** - Complete mock payment process
5. **View Booking** - Access booking details and manage reservations

### For Admins

1. **Login** - Use admin credentials: `admin@airline.com` / `admin123`
2. **Dashboard** - View revenue metrics, load factors, and booking trends
3. **Manage Flights** - Monitor seat availability and pricing
4. **Configure Pricing** - Adjust pricing factors and fare rules
5. **Overbooking** - Configure overbooking settings per flight

## 🔧 Background Jobs

The system runs several automated tasks:

| Job | Frequency | Purpose |
|-----|-----------|---------|
| `bookings:release-expired` | Every minute | Releases seats from expired holds |
| `pricing:update` | Hourly | Updates flight prices based on demand |
| `demand:decay` | Hourly | Decays demand scores naturally |
| `demand:proximity-boost` | Daily | Increases demand for near-departure flights |
| `demand:simulate` | Every 15 minutes | Simulates searches and bookings |
| `overbooking:enforce-rules` | Hourly | Enforces overbooking safety rules |
| `bookings:send-checkin-reminders` | Every 6 hours | Sends check-in reminder emails |
| `flights:generate-daily` | Daily at 2 AM | Maintains 30-day flight window |

## 🎨 Additional Features

### Dark Mode
Toggle dark mode from the user menu in the navigation bar.

### Booking History
View all past and upcoming bookings from your profile page.

### Favorite Routes
Save frequently searched routes for quick access.

### Price Calendar
View price trends across different dates for better booking decisions.

### Flight Status
Check real-time flight status including delays and cancellations.

## 🔑 Default Credentials

**Admin Access:**
- Email: `admin@airline.com`
- Password: `admin123`

**Regular User:**
- Email: `user@airline.com`
- Password: `user123`

## 📊 Database Schema

The system uses 42 migrations creating tables for:
- Users and authentication
- Aircraft and flights
- Fare classes and rules
- Seats and bookings
- Passengers and check-ins
- Price history and demand tracking
- Add-ons and payment methods
- Audit logs and system logs

## 🛠️ Troubleshooting

### Queue jobs not running
Ensure `php artisan queue:work` is running in a separate terminal.

### Prices not updating
Check that `php artisan schedule:work` is running or cron job is configured.

### "No seats available" but I see empty seats
Check if seats are in "held" status (visible in admin dashboard). They'll be released after 15 minutes.

### Database connection issues
Verify database credentials in `.env` and ensure MySQL is running.

### Assets not loading
Run `npm run build` to compile frontend assets.

## 📖 Documentation

Additional technical documentation is available in the repository:

- `SYSTEM_OVERVIEW.md` - High-level system architecture
- `FEATURES.md` - Complete feature list and roadmap
- `TECHNICAL_DOCUMENTATION.md` - Deep dives into pricing, inventory, and concurrency
- `DATABASE_SCHEMA.md` - Database design and relationships
- `PRICING_ALGORITHM.md` - Detailed pricing formula explanation
- `TESTING_GUIDE.md` - How to test the system

## 🎯 Tech Stack

- **Backend:** Laravel 10 (PHP 8.1+)
- **Frontend:** Blade Templates, Tailwind CSS, Alpine.js
- **Database:** MySQL 8.0+
- **Queue:** Laravel Queue (Database Driver)
- **Assets:** Vite
- **Notifications:** SweetAlert2
- **PDF Generation:** DomPDF

## 📝 License

This project is open-source and available under the MIT License.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📧 Support

For issues or questions, please open an issue on GitHub or contact the maintainer.

---

**Built with Laravel ❤️**
