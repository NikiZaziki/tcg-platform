# Multi-Game TCG Platform

A comprehensive Trading Card Game (TCG) web platform supporting multiple TCGs (Pokémon, Yu-Gi-Oh, Magic: The Gathering) with digital card collection, booster pack economy, online competitive gameplay, card trading, and daily rewards.

## Features

- **Multi-Game Support**: Platform supports multiple TCGs simultaneously
- **Digital Card Collection**: Users can collect and manage cards digitally
- **Booster Pack Economy**: Server-side RNG pack opening system with rarity drops
- **Online Competitive Gameplay**: Normal and ranked match modes
- **Card Trading**: Peer-to-peer card trading system
- **Daily Rewards**: Free daily booster pack for all users
- **Deck Building**: Create and validate custom decks
- **Ranked Mode**: Competitive matches with card transfer penalties for losers
- **Real-time Features**: WebSocket support for live matches

## Technology Stack

### Backend
- **Framework**: NestJS (TypeScript)
- **Database**: MySQL with Prisma ORM
- **Authentication**: JWT with Passport
- **Real-time**: WebSocket support
- **Caching**: Redis for matchmaking and sessions
- **Node.js**: v26 or higher required

### Frontend
- **Framework**: Next.js 14+ with App Router
- **Language**: TypeScript
- **Styling**: Tailwind CSS
- **State Management**: React hooks
- **Real-time**: WebSocket client

## Project Structure

```
TCG/
+-- backend/                 # NestJS backend application
|   +-- src/
|   |   +-- modules/        # Feature modules
|   |   |   +-- auth/       # Authentication module
|   |   |   +-- tcg/        # TCG management module
|   |   |   +-- inventory/  # Inventory management module
|   |   |   +-- pack/       # Pack opening module
|   |   |   +-- shop/       # Shop module
|   |   |   +-- deck/       # Deck building module
|   |   |   +-- matchmaking/# Matchmaking module
|   |   |   +-- match/      # Match gameplay module
|   |   |   +-- trading/    # Trading module
|   |   |   +-- reward/     # Daily rewards module
|   |   +-- common/         # Shared utilities
|   |   |   +-- guards/     # Auth guards
|   |   |   +-- decorators/ # Custom decorators
|   |   |   +-- interceptors/# Interceptors
|   |   |   +-- utils/      # Utility functions
|   |   +-- config/         # Configuration files
|   |   +-- main.ts         # Application entry point
|   +-- prisma/
|   |   +-- schema.prisma   # Database schema
|   |   +-- seed.ts         # Database seed data
|   |   +-- migrations/     # Database migrations
|   +-- package.json
+-- frontend/               # Next.js frontend application
|   +-- app/                # App router pages
|   +-- components/         # React components
|   +-- lib/                # Utility libraries
|   +-- hooks/              # Custom React hooks
|   +-- types/              # TypeScript types
|   +-- package.json
+-- README.md
```

## Database Schema

The platform uses MySQL with the following core entities:

- **Users**: User accounts with authentication and ranking
- **TCG**: Supported trading card games
- **Cards**: Individual cards with stats and abilities
- **Rarity**: Card rarity levels with drop rates
- **Packs**: Booster packs with drop tables
- **Inventory**: User card collections
- **Decks**: User-created decks
- **Matches**: Game matches and results
- **RankedTransfers**: Card transfers from ranked matches
- **Trades**: Peer-to-peer trading system
- **Orders**: Shop purchase history

## Prerequisites

- Node.js v26 or higher
- MySQL 8.0 or higher
- Redis 6.0 or higher
- npm or yarn package manager

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd TCG
```

### 2. Backend Setup

```bash
cd backend

# Install dependencies
npm install

# Configure environment variables
cp .env.example .env
# Edit .env with your database credentials

# Generate Prisma client
npx prisma generate

# Run database migrations
npx prisma migrate dev

# Seed the database with initial data
npm run seed

# Start the backend server
npm run start:dev
```

### 3. Frontend Setup

```bash
cd frontend

# Install dependencies
npm install

# Start the frontend development server
npm run dev
```

## Environment Variables

### Backend (.env)

```env
DATABASE_URL="mysql://username:password@localhost:3306/tcg_platform"
JWT_SECRET="your-secret-key-change-this-in-production"
JWT_EXPIRATION="7d"
REDIS_HOST="localhost"
REDIS_PORT="6379"
PORT="3000"
```

### Frontend (.env.local)

```env
NEXT_PUBLIC_API_URL="http://localhost:3000"
NEXT_PUBLIC_WS_URL="ws://localhost:3000"
```

## API Endpoints

### Authentication
- `POST /api/auth/register` - User registration
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/me` - Get current user

### TCG Management
- `GET /api/tcg` - List all TCGs
- `GET /api/tcg/:id` - Get TCG details
- `GET /api/tcg/:id/cards` - Get cards for TCG
- `GET /api/tcg/:id/packs` - Get packs for TCG

### Inventory
- `GET /api/inventory` - Get user inventory
- `GET /api/inventory/:cardId` - Get card quantity

### Shop
- `GET /api/shop/packs` - List available packs
- `POST /api/shop/orders` - Create order
- `GET /api/shop/orders` - Get order history

### Pack Opening
- `POST /api/packs/open` - Open a pack
- `GET /api/packs/openings` - Get pack opening history

### Deck Management
- `GET /api/decks` - List user decks
- `POST /api/decks` - Create deck
- `GET /api/decks/:id` - Get deck details
- `PUT /api/decks/:id` - Update deck
- `DELETE /api/decks/:id` - Delete deck
- `POST /api/decks/:id/cards` - Add card to deck
- `DELETE /api/decks/:id/cards/:cardId` - Remove card from deck
- `GET /api/decks/:id/validate` - Validate deck

### Matchmaking
- `POST /api/matchmaking/queue` - Join matchmaking queue
- `DELETE /api/matchmaking/queue` - Leave matchmaking queue
- `GET /api/matchmaking/status` - Get queue status

### Matches
- `GET /api/matches` - Get user matches
- `GET /api/matches/:id` - Get match details
- `POST /api/matches/:id/moves` - Submit move
- `GET /api/matches/:id/history` - Get match history

### Trading
- `POST /api/trades` - Create trade proposal
- `GET /api/trades` - Get user trades
- `GET /api/trades/:id` - Get trade details
- `PUT /api/trades/:id/accept` - Accept trade
- `PUT /api/trades/:id/reject` - Reject trade
- `DELETE /api/trades/:id` - Cancel trade

### Rewards
- `GET /api/rewards/daily` - Get daily reward status
- `POST /api/rewards/daily/claim` - Claim daily pack

## Deployment

### Linux vServer Deployment (http://45.131.111.6/TCG)

#### 1. Server Preparation

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Install Node.js v26
curl -fsSL https://deb.nodesource.com/setup_26.x | sudo -E bash -
sudo apt install -y nodejs

# Install MySQL
sudo apt install -y mysql-server

# Install Redis
sudo apt install -y redis-server

# Install Nginx (for reverse proxy)
sudo apt install -y nginx

# Install PM2 (for process management)
sudo npm install -g pm2
```

#### 2. Database Setup

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database and user
sudo mysql -u root -p
```

```sql
CREATE DATABASE tcg_platform;
CREATE USER 'tcg_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON tcg_platform.* TO 'tcg_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### 3. Application Setup

```bash
# Clone repository to server
cd /var/www
git clone <repository-url> TCG
cd TCG

# Backend setup
cd backend
npm install
cp .env.example .env
# Edit .env with production values
npx prisma generate
npx prisma migrate deploy
npm run seed

# Frontend setup
cd ../frontend
npm install
npm run build
```

#### 4. Configure Environment Variables

Edit `backend/.env`:

```env
DATABASE_URL="mysql://tcg_user:your_secure_password@localhost:3306/tcg_platform"
JWT_SECRET="production-secret-key-change-this-immediately"
JWT_EXPIRATION="7d"
REDIS_HOST="localhost"
REDIS_PORT="6379"
PORT="3000"
NODE_ENV="production"
```

#### 5. Start Services with PM2

```bash
# Start backend
cd /var/www/TCG/backend
pm2 start dist/main.js --name tcg-backend

# Start frontend (if using standalone Next.js server)
cd /var/www/TCG/frontend
pm2 start npm --name tcg-frontend -- start

# Save PM2 configuration
pm2 save
pm2 startup
```

#### 6. Configure Nginx

Create `/etc/nginx/sites-available/tcg-platform`:

```nginx
server {
    listen 80;
    server_name 45.131.111.6;

    # Frontend
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    # Backend API
    location /api {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    # WebSocket support
    location /ws {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/tcg-platform /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

#### 7. Configure SSL (Optional but Recommended)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain SSL certificate
sudo certbot --nginx -d 45.131.111.6

# Auto-renewal is configured automatically
```

#### 8. Start Redis

```bash
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

#### 9. Configure Firewall

```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

## Development

### Running Development Servers

```bash
# Terminal 1: Backend
cd backend
npm run start:dev

# Terminal 2: Frontend
cd frontend
npm run dev
```

### Database Management

```bash
# Create migration
npx prisma migrate dev --name migration_name

# Reset database (development only)
npx prisma migrate reset

# View database in Prisma Studio
npx prisma studio
```

### Testing

```bash
# Backend tests
cd backend
npm test

# Frontend tests
cd frontend
npm test
```

## Security Considerations

- All RNG operations are server-side
- JWT tokens for authentication
- Password hashing with bcrypt
- Input validation and sanitization
- SQL injection prevention via Prisma
- Rate limiting on sensitive endpoints
- CORS configuration for API access

## Performance Optimization

- Redis caching for frequently accessed data
- Database indexing for common queries
- Lazy loading for large card collections
- Code splitting in Next.js
- Image optimization for card assets

## Troubleshooting

### Database Connection Issues

```bash
# Check MySQL status
sudo systemctl status mysql

# Test connection
mysql -u tcg_user -p tcg_platform
```

### Redis Connection Issues

```bash
# Check Redis status
sudo systemctl status redis-server

# Test connection
redis-cli ping
```

### PM2 Process Issues

```bash
# View logs
pm2 logs tcg-backend
pm2 logs tcg-frontend

# Restart services
pm2 restart tcg-backend
pm2 restart tcg-frontend

# Monitor processes
pm2 monit
```

### Nginx Issues

```bash
# Check Nginx status
sudo systemctl status nginx

# Test configuration
sudo nginx -t

# View error logs
sudo tail -f /var/log/nginx/error.log
```

## Game Mechanics

### Ranked Mode Rules
- Loser must transfer one random card from their deck to the winner
- Card transfer is atomic and validated server-side
- Transfer history is recorded for audit purposes

### Daily Rewards
- Each user receives 1 free booster pack every 24 hours
- Cooldown timer prevents abuse
- Pack type varies based on user rank tier

### Pack Opening
- Server-side RNG ensures fair card distribution
- Drop tables define rarity probabilities
- Opening history is recorded for all packs

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the ISC License.

## Support

For issues and questions, please open an issue on the repository or contact the development team.

## Acknowledgments

- NestJS team for the excellent framework
- Next.js team for the React framework
- Prisma team for the ORM
- The open-source community