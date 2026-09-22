# MulaCargo 🫏📦

> **O frete inteligente que nunca volta vazio.**  
> Smart freight bidding & backhaul optimization marketplace.

---

## 🏗 Project Overview

**MulaCargo** is an on-demand freight and cargo logistics platform designed specifically for pickups, vans, box trucks, and heavy carriers. It replaces conventional high-fee middlemen with a direct bidding marketplace and an intelligent return-route matching engine.

### Key Pillars
- **Direct Bidding:** Shippers publish cargo requirements; drivers bid in real-time.
- **3.5% Platform Fee:** Flat, transparent 3.5% take rate per completed trip.
- **Radar de Retorno (Backhaul Engine):** Suggests return freight along the driver's homeward route to eliminate empty miles ("rodagem vazia") and maximize net profit.
- **Heavy Cargo Focus:** Categorized for pickups (Strada, Hilux), cargo vans (Fiorino, Sprinter), medium trucks (3/4, Toco), and heavy haulers.

---

## 📁 Repository Structure

```
/root/ClubeMkt/mulacargo/
├── apps/
│   ├── user-app/       # React Native app for shippers & cargo senders
│   └── driver-app/     # React Native app for carriers, truckers & pickup drivers
├── backend/            # Laravel API, Admin Dashboard & Webhook Engine
├── docs/               # Architecture, Brand Guides, Specs & AI Asset Prompts
│   ├── BRANDING_GUIDE.md   # Visual identity, colors, typography, voice & tone
│   ├── ASSET_PROMPTS.md    # Midjourney / DALL-E 3 / Flux image generation prompts
│   └── upstream-docs/      # Upstream base documentation and update logs
├── _archives/          # Original distribution source archives
└── .gitignore          # Monorepo-level git configuration
```

---

## 🚀 Getting Started

### 1. Backend (Laravel API & Admin)
```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### 2. User App (Shippers / React Native)
```bash
cd apps/user-app
npm install  # or yarn / pnpm
npm run start
```

### 3. Driver App (Carriers / React Native)
```bash
cd apps/driver-app
npm install  # or yarn / pnpm
npm run start
```

---

## 🎨 Branding & Assets

See:
- [`docs/BRANDING_GUIDE.md`](./docs/BRANDING_GUIDE.md) for color codes, typography, voice, and vehicle taxonomy.
- [`docs/ASSET_PROMPTS.md`](./docs/ASSET_PROMPTS.md) for 3D mascot, vehicle render, badge, and marketing prompts.
