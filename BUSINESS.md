# Sport Gear E-Commerce Business Plan

## Business Overview

### Business Name
**RED E.F.T Sports** (Example)

### Business Concept
An online e-commerce store specializing in football equipment and apparel, offering products like football boots, jerseys, balls, and training gear. The store combines a modern, dynamic e-commerce platform with a customizable filter system, enabling football players and fans in Cambodia to find the exact gear they need quickly and efficiently.

---

## Target Audience

1. **Football Players** - All skill levels (amateur to professional)
2. **Football Fans** - Looking for jerseys or team merchandise
3. **Sports Enthusiasts** - Seeking high-quality football gear
4. **Cambodian Market** - Primary focus with localized payment and shipping options

---

## Product Categories

### 1. Football Boots
- Various brands (Nike, Adidas, Puma, New Balance, etc.)
- Different surface types (Firm Ground, Soft Ground, Artificial Grass, Indoor Court, Turf)
- Multiple sizes (39-45 for adults)
- Premium to budget options

### 2. Jerseys
- Club jerseys (Barcelona, Real Madrid, Manchester United, etc.)
- League jerseys (Premier League, La Liga, Bundesliga, Serie A)
- Jersey types: Home, Away, Third, Goalkeeper
- Multiple sizes (S, M, L, XL, XXL)

### 3. Football Balls
- Match balls
- Training balls
- Different sizes (Size 3, 4, 5)
- Official league balls

### 4. Training Gear
- Training jerseys
- Shorts
- Socks
- Shin guards
- Goalkeeper gloves
- Training cones and equipment

---

## Key Features & Unique Selling Points

### 1. Dynamic Product Filtering System
**Problem**: Customers waste time browsing through irrelevant products.

**Solution**: Category-specific intelligent filters.

- **For Football Boots**: Filter by Brand → Surface Type
- **For Jerseys**: Filter by Brand, League → Team
- **Dependent Filters**: Selecting a league shows only teams from that league
- **Fast Results**: Optimized queries for instant filtering

**Example User Journey**:
1. Customer visits "Jerseys" category
2. Selects "Premier League" → Only Premier League teams appear
3. Selects "Manchester United" → All Man United jerseys shown (Home, Away, Third, GK)
4. Selects size "L" → Only available L sizes displayed

### 2. Smart Discount System (Dual Mechanism)

#### A. Manual Coupons (Admin-Created)
- **Example**: "NEWYEAR2026" for 15% off
- Minimum purchase requirements
- Usage limits
- Date-based validity
- **Use Case**: Seasonal promotions, special events, marketing campaigns

#### B. Automatic Loyalty Tiers (Phone-Based)
**How It Works**:
- System tracks total purchase amount per phone number
- Automatic tier assignment and discount application

**Tiers**:
- **None**: Less than $50 total spent → 0% discount
- **Silver**: $50-$99 total spent → 5% automatic discount
- **Gold**: $100+ total spent → 10% automatic discount

**Business Logic**:
- If customer has Gold tier (10%) AND enters coupon (15%) → Apply 15% only (best discount wins)
- Cannot stack discounts (prevents abuse)
- Tied to phone number (works even for guest checkout)

**Example Scenario**:
- Customer A has spent $60 total (Silver tier)
- Places $30 order → Gets 5% off automatically ($1.50 discount)
- After delivery, total spent = $88.50 (still Silver)
- Next order $25 → Total spent reaches $113.50 → Upgraded to Gold (10% future discounts)

### 3. Product Variant Management

**Design Decision**: Each color is a SEPARATE product (not variants)

**Why?**
- Different jerseys (Home/Away/Third) have different designs, not just colors
- Different boot colorways are separate marketing products
- Simplifies inventory and pricing

**What ARE Variants?**
- **Sizes only**: S, M, L, XL, XXL (apparel) or 39, 40, 41, 42, 43, 44, 45 (boots)
- Stock tracked per size
- Optional price adjustments (e.g., size 45+ boots cost $5 more)

**Example**:
```
Product 1: Manchester United Home Jersey 2024/25 (Red)
  - Variants: S (20 stock), M (35), L (40), XL (25), XXL (15)

Product 2: Manchester United Away Jersey 2024/25 (White)
  - Variants: S (15), M (30), L (35), XL (20), XXL (10)

Product 3: Nike Mercurial Vapor 15 FG Blue/Volt
  - Variants: 39 ($120), 40 ($120), 41 ($120), 42 ($120), 43 ($120), 44 ($120), 45 ($125)
```

### 4. Payment & Shipping Strategy

#### Payment Methods
1. **Cash on Delivery (COD)**
   - **Available**: Phnom Penh only
   - **Why**: Higher trust, easier verification
   - **Risk**: Lower for short-distance deliveries

2. **KHQR (QR Code Payment)**
   - **Available**: All other provinces
   - **Process**: Customer pays → uploads screenshot → admin verifies → order confirmed
   - **Why**: Secure, traceable, standard in Cambodia

#### Shipping Fees
- **Province-based pricing** (admin-configurable)
- **Example**:
  - Phnom Penh: Free or $2
  - Siem Reap: $5
  - Sihanoukville: $6
  - Remote provinces: $8

### 5. Guest Cart with Seamless Login
**User Experience**:
1. Guest browses and adds items to cart (stored in session)
2. Guest decides to checkout → Required to login/register
3. Upon login, guest cart automatically merges with user cart
4. Checkout with saved addresses and loyalty discount applied

**Benefits**:
- No friction during browsing
- Captures email/phone at checkout
- Applies loyalty discount if phone number matches existing purchases

### 6. Verified Purchase Reviews
**Trust Building**:
- Only customers who purchased can review
- "Verified Purchase" badge on reviews
- 1-5 star ratings
- Upload images with reviews
- Admin moderation to prevent spam

**Social Proof**:
- Display review count and average rating on products
- "Was this helpful?" voting system

### 7. Wishlist/Favorites
- Save products for later
- Share wishlist via link
- Notifications when wishlist items go on sale (future feature)

---

## Revenue Model

### Primary Revenue Streams
1. **Direct Product Sales**
   - Markup on wholesale prices
   - Competitive pricing vs. physical stores
   - Volume-based profits

2. **Seasonal Promotions**
   - World Cup periods
   - League season starts
   - Holiday sales (New Year, Independence Day)

3. **Loyalty Program**
   - Encourages repeat purchases
   - Higher customer lifetime value

### Pricing Strategy
- **Competitive Pricing**: Match or beat local store prices
- **Premium Products**: Higher margins on exclusive items
- **Volume Discounts**: Bulk orders (team orders)

---

## Marketing Strategy

### 1. SEO-Friendly URLs
All products have clean, search-optimized URLs:
- `/football-boots`
- `/brand/nike`
- `/team/barcelona`
- `/football-boots/nike-mercurial-vapor-15-fg-blue-volt`

### 2. Social Media Integration
- Share products on Facebook, Instagram, TikTok
- Influencer partnerships with local football players
- User-generated content (photos of customers wearing jerseys)

### 3. Coupon Marketing
- First-time buyer discount codes
- Seasonal campaigns ("WORLDCUP2026" for 20% off)
- Email marketing with exclusive codes

### 4. Loyalty Tier Visibility
- Dashboard showing customer's tier and total spent
- "Spend $15 more to reach Gold tier!" notifications
- Badge display (Silver/Gold) to encourage status

---

## Competitive Advantages

### 1. Dynamic Filtering
**vs. Traditional E-Commerce**:
- Most local stores have basic category browsing
- Our system adapts filters per category intelligently
- Faster product discovery

### 2. Phone-Based Loyalty
**vs. Traditional Loyalty Cards**:
- No physical card needed
- Works for guest checkout
- Automatic tier calculation
- Tied to phone number (most Cambodians remember their number)

### 3. Dual Discount System
**vs. Single Coupon Systems**:
- Rewards loyal customers automatically
- Still allows marketing campaigns
- Best-discount logic prevents confusion

### 4. Province-Based Payments
**vs. One-Size-Fits-All**:
- COD for Phnom Penh (high trust)
- KHQR for provinces (secure, traceable)
- Optimized for Cambodian payment behavior

---

## Customer Journey Examples

### Scenario 1: First-Time Buyer
1. **Discovery**: Searches "Nike Mercurial boots Cambodia" on Google
2. **Landing**: Arrives at `/football-boots`
3. **Filtering**: Selects Brand: Nike, Surface: Firm Ground
4. **Product**: Clicks "Nike Mercurial Vapor 15 FG Blue/Volt"
5. **Variant**: Selects size 42
6. **Cart**: Adds to cart (guest cart)
7. **Checkout**: Enters phone number, creates account
8. **Payment**: Lives in Siem Reap → Pays via KHQR, uploads screenshot
9. **Confirmation**: Order confirmed after payment verification
10. **Delivery**: Receives boots in 3-5 days
11. **Review**: Leaves 5-star review with photo

**Outcome**: Total spent $120 → Next order gets Silver discount

### Scenario 2: Loyal Customer (Gold Tier)
1. **Login**: Logs in (already has $110 total spent - Gold tier)
2. **Wishlist**: Checks wishlist, sees Barcelona away jersey
3. **Add to Cart**: Adds size L to cart
4. **Checkout**: Price $85 → 10% Gold discount applied → $76.50
5. **Saved Address**: Selects saved "Home" address in Phnom Penh
6. **Payment**: Cash on Delivery
7. **Confirmation**: Order confirmed immediately
8. **Delivery**: Same-day delivery in Phnom Penh

**Outcome**: Total spent now $186.50 → Maintains Gold tier

### Scenario 3: Team Bulk Order
1. **Contact**: Local team manager contacts via phone
2. **Bulk Order**: Needs 25 jerseys (same design, different sizes)
3. **Custom Quote**: Admin creates custom coupon "TEAM25" for 15% off
4. **Order**: Manager places order, enters coupon
5. **Payment**: KHQR or bank transfer
6. **Delivery**: Shipped to team practice location

**Outcome**: High-value order, potential for repeat team orders

---

## Success Metrics (KPIs)

### 1. Sales Metrics
- Monthly revenue
- Average order value (AOV)
- Conversion rate
- Cart abandonment rate

### 2. Customer Metrics
- New vs. returning customers
- Customer lifetime value (CLV)
- Loyalty tier distribution (% Silver, % Gold)
- Review rate per order

### 3. Product Metrics
- Best-selling categories
- Best-selling brands
- Stock turnover rate
- Out-of-stock incidents

### 4. Marketing Metrics
- Coupon usage rate
- Coupon ROI
- Organic search traffic
- Social media referrals

---

## Future Expansion Ideas

### Phase 2 Features
1. **Stock Alerts**: Notify users when out-of-stock items are back
2. **Product Comparison**: Compare multiple boots side-by-side
3. **Size Guide**: Interactive size recommendation tool
4. **Live Chat**: Real-time customer support
5. **Mobile App**: Native iOS/Android app

### Phase 3 Features
1. **Custom Jersey Printing**: Add name/number to jerseys
2. **Pre-Orders**: Reserve upcoming product launches
3. **Subscription Box**: Monthly gear subscription
4. **Team Portal**: Dedicated B2B portal for team managers
5. **AR Try-On**: Augmented reality for jerseys

### Geographic Expansion
- Expand to other Southeast Asian countries
- Partnerships with regional teams
- Multi-currency support

---

## Risk Mitigation

### 1. Counterfeit Products
**Risk**: Customers doubt authenticity of branded products.

**Mitigation**:
- Source from authorized distributors only
- Display supplier certifications
- "100% Authentic" guarantee
- Easy returns for suspected fakes

### 2. Shipping Damage
**Risk**: Products damaged during delivery.

**Mitigation**:
- Quality packaging
- Insurance for high-value orders
- Photo proof of packaging
- Easy replacement process

### 3. Payment Fraud (KHQR)
**Risk**: Fake payment screenshots.

**Mitigation**:
- Manual verification by admin
- Cross-check with bank transaction IDs
- Only ship after payment confirmation
- Blacklist fraudulent phone numbers

### 4. Stock Management
**Risk**: Overselling out-of-stock items.

**Mitigation**:
- Real-time stock tracking per variant
- Low-stock alerts
- Automatic "Out of Stock" status
- Reserve stock on checkout (temp hold)

---

## Technology Stack Summary

### Backend (Admin Panel)
- **Laravel 12**: Robust PHP framework
- **Inertia.js + Vue 3**: Modern admin interface
- **Spatie Permission**: Role-based access control
- **MySQL**: Reliable relational database

### Frontend (Customer-Facing)
- **Nuxt 3**: Fast, SEO-optimized framework
- **Sanctum SPA Auth**: Secure authentication
- **Tailwind CSS**: Modern, responsive design

### Infrastructure
- **Storage**: Laravel Storage with symlinks
- **Images**: Optimized with thumbnails
- **Email**: Transactional emails (order confirmations)
- **Queue**: Background jobs for heavy tasks

---

## Conclusion

This e-commerce platform is specifically designed for the Cambodian football gear market with:
- ✅ Smart filtering for faster product discovery
- ✅ Dual discount system (coupons + loyalty) to drive repeat purchases
- ✅ Localized payment options (COD + KHQR)
- ✅ Province-specific shipping fees
- ✅ Scalable architecture for future growth

The combination of user-friendly features, smart business logic, and modern technology positions RED E.F.T Sports as a leader in the Cambodian sports e-commerce market.
