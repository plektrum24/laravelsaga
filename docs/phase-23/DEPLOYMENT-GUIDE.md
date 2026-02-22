# SAGA POS Mobile App - Deployment Guide

**Version:** 2.1.0  
**Date:** 2026-02-22  
**Platform:** iOS & Android (React Native / Expo)

---

## 📋 Table of Contents

1. [Prerequisites](#prerequisites)
2. [Pre-Deployment Checklist](#pre-deployment-checklist)
3. [Build Configuration](#build-configuration)
4. [iOS Deployment](#ios-deployment)
5. [Android Deployment](#android-deployment)
6. [Post-Deployment](#post-deployment)
7. [Monitoring & Maintenance](#monitoring--maintenance)
8. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Required Accounts
- [ ] Expo Account (https://expo.dev)
- [ ] Apple Developer Account ($99/year)
- [ ] Google Play Console Account ($25 one-time)
- [ ] Firebase Project (for push notifications)
- [ ] Midtrans Account (for payments)

### Required Software
```bash
# Node.js (v18 or higher)
node --version

# Expo CLI
npm install -g expo-cli

# EAS CLI (for building)
npm install -g eas-cli

# Verify installations
expo --version
eas --version
```

### Environment Setup
```bash
# Navigate to mobile app directory
cd mobile-app

# Install dependencies
npm install

# Verify all dependencies installed
npx expo-doctor
```

---

## Pre-Deployment Checklist

### Code Quality
- [ ] All TypeScript errors resolved
- [ ] No console.log in production code
- [ ] All components tested
- [ ] No hardcoded API URLs (use environment variables)
- [ ] Error handling implemented everywhere

### Testing
- [ ] Login/Register flow tested
- [ ] Product browsing tested
- [ ] Add to cart tested
- [ ] Checkout flow tested
- [ ] Payment integration tested (sandbox)
- [ ] Order tracking tested
- [ ] Push notifications tested
- [ ] Loyalty features tested

### Configuration
- [ ] `app.json` updated with correct bundle IDs
- [ ] Environment variables configured
- [ ] API endpoints pointing to production
- [ ] Midtrans credentials updated
- [ ] Firebase configuration complete

### Assets
- [ ] App icon (1024x1024 PNG)
- [ ] Splash screen (1242x2436 PNG)
- [ ] Notification icon (1024x1024 PNG)
- [ ] Screenshots for app stores

---

## Build Configuration

### 1. Update app.json

```json
{
  "expo": {
    "name": "SAGA POS",
    "slug": "saga-pos",
    "version": "2.1.0",
    "orientation": "portrait",
    "icon": "./assets/images/icon.png",
    "scheme": "sagapos",
    "userInterfaceStyle": "automatic",
    "newArchEnabled": true,
    
    "splash": {
      "image": "./assets/images/splash-icon.png",
      "resizeMode": "contain",
      "backgroundColor": "#4F46E5"
    },
    
    "ios": {
      "supportsTablet": true,
      "bundleIdentifier": "com.sagaposo.mobileapp",
      "buildNumber": "1",
      "infoPlist": {
        "NSCameraUsageDescription": "Scan product barcodes",
        "NSLocationWhenInUseUsageDescription": "Find nearby stores",
        "NSPhotoLibraryUsageDescription": "Upload product reviews"
      }
    },
    
    "android": {
      "adaptiveIcon": {
        "foregroundImage": "./assets/images/adaptive-icon.png",
        "backgroundColor": "#4F46E5"
      },
      "package": "com.sagaposo.mobileapp",
      "versionCode": 1,
      "edgeToEdgeEnabled": true,
      "permissions": [
        "CAMERA",
        "ACCESS_FINE_LOCATION",
        "ACCESS_COARSE_LOCATION",
        "POST_NOTIFICATIONS"
      ]
    },
    
    "web": {
      "bundler": "metro",
      "output": "static",
      "favicon": "./assets/images/favicon.png"
    },
    
    "plugins": [
      "expo-router",
      [
        "expo-notifications",
        {
          "icon": "./assets/images/notification-icon.png",
          "color": "#4F46E5",
          "sounds": ["./assets/notification-sound.wav"]
        }
      ],
      [
        "expo-camera",
        {
          "cameraPermission": "Allow SAGA POS to access your camera for scanning barcodes."
        }
      ],
      [
        "expo-location",
        {
          "locationAlwaysAndWhenInUsePermission": "Allow SAGA POS to use your location for finding nearby stores."
        }
      ]
    ],
    
    "extra": {
      "eas": {
        "projectId": "your-project-id-from-expo"
      }
    }
  }
}
```

### 2. Create .env File

```env
# API Configuration
EXPO_PUBLIC_API_URL=https://api.sagaposo.com/api
EXPO_PUBLIC_API_TIMEOUT=30000

# Firebase Configuration
EXPO_PUBLIC_FIREBASE_API_KEY=your_api_key
EXPO_PUBLIC_FIREBASE_AUTH_DOMAIN=your_project.firebaseapp.com
EXPO_PUBLIC_FIREBASE_PROJECT_ID=your_project_id
EXPO_PUBLIC_FIREBASE_STORAGE_BUCKET=your_project.appspot.com
EXPO_PUBLIC_FIREBASE_MESSAGING_SENDER_ID=your_sender_id
EXPO_PUBLIC_FIREBASE_APP_ID=your_app_id
EXPO_PUBLIC_FIREBASE_VAPID_KEY=your_vapid_key

# Midtrans Configuration
EXPO_PUBLIC_MIDTRANS_CLIENT_KEY=Mid-client-xxxxx
EXPO_PUBLIC_MIDTRANS_IS_PRODUCTION=true

# App Configuration
EXPO_PUBLIC_APP_VERSION=2.1.0
EXPO_PUBLIC_SENTRY_DSN=https://your-sentry-dsn
```

### 3. Configure EAS Build

Create `eas.json`:

```json
{
  "cli": {
    "version": ">= 5.0.0"
  },
  "build": {
    "development": {
      "developmentClient": true,
      "distribution": "internal"
    },
    "preview": {
      "distribution": "internal",
      "ios": {
        "simulator": true
      }
    },
    "production": {
      "ios": {
        "resourceClass": "m-medium"
      },
      "android": {
        "buildType": "app-bundle"
      }
    }
  },
  "submit": {
    "production": {
      "ios": {
        "appleId": "your-apple-id",
        "ascAppId": "your-app-store-connect-app-id",
        "appleTeamId": "your-team-id"
      },
      "android": {
        "serviceAccountKeyPath": "./google-service-account.json",
        "track": "internal"
      }
    }
  }
}
```

---

## iOS Deployment

### Step 1: Configure Apple Developer Account

1. Login to Apple Developer Portal
2. Create App ID: `com.sagaposo.mobileapp`
3. Enable capabilities:
   - Push Notifications
   - Associated Domains (if using universal links)
4. Create Distribution Certificate
5. Create Provisioning Profile (App Store)

### Step 2: Configure App Store Connect

1. Login to App Store Connect
2. Create New App
3. Fill in app information:
   - **Name:** SAGA POS
   - **Primary Language:** English
   - **Bundle ID:** com.sagaposo.mobileapp
   - **SKU:** SAGA-POS-001
   - **User Access:** Full Access

4. Prepare App Store Listing:
   - **Description:** Your complete shopping companion
   - **Keywords:** shopping, pos, retail, loyalty
   - **Support URL:** https://sagaposo.com/support
   - **Marketing URL:** https://sagaposo.com
   - **Privacy Policy URL:** https://sagaposo.com/privacy

5. Upload Screenshots:
   - 6.5" Display (1284 x 2778): 5 screenshots
   - 5.5" Display (1242 x 2208): 5 screenshots
   - iPad Pro (2048 x 2732): 5 screenshots (optional)

### Step 3: Build with EAS

```bash
# Login to Expo
eas login

# Configure EAS (first time only)
eas build:configure

# Build for iOS
eas build --platform ios --profile production

# Monitor build status
eas build:list

# Download build
eas build:download --platform ios --id=BUILD_ID
```

### Step 4: Submit to App Store

```bash
# Submit to App Store Connect
eas submit --platform ios --latest

# Or manually upload via Xcode:
# 1. Download .ipa file
# 2. Open Xcode → Window → Organizer
# 3. Select app → Distribute App → App Store Connect
```

### Step 5: App Store Review

**Common Requirements:**
- [ ] App completes core functionality
- [ ] No broken links or features
- [ ] Privacy policy included
- [ ] Terms of service included
- [ ] Proper age rating
- [ ] No placeholder content
- [ ] All screenshots match app

**Review Time:** 24-48 hours typically

---

## Android Deployment

### Step 1: Configure Google Play Console

1. Login to Google Play Console
2. Create App
3. Fill in app information:
   - **App Name:** SAGA POS
   - **Package Name:** com.sagaposo.mobileapp
   - **Default Language:** English (United States)

4. Complete Store Listing:
   - **Short Description:** Your complete shopping companion (80 chars)
   - **Full Description:** Detailed description (4000 chars)
   - **App Icon:** 512x512 PNG
   - **Feature Graphic:** 1024x500 PNG
   - **Screenshots:** At least 2 for phone, 7 for tablet (recommended)

5. Content Rating:
   - Complete questionnaire
   - Get rating certificate

6. Pricing & Distribution:
   - Select countries
   - Set price (Free/Paid)

### Step 2: Generate Upload Key

```bash
# Generate keystore (first time only)
keytool -genkey -v -keystore saga-pos.keystore -alias saga-pos -keyalg RSA -keysize 2048 -validity 10000

# Store keystore securely!
# You'll need this for all future updates
```

### Step 3: Build with EAS

```bash
# Build for Android
eas build --platform android --profile production

# Monitor build status
eas build:list

# Download build
eas build:download --platform android --id=BUILD_ID
```

### Step 4: Upload to Play Store

```bash
# Submit to Google Play
eas submit --platform android --latest

# Or manually:
# 1. Download .aab file
# 2. Go to Play Console → Production → Create Release
# 3. Upload .aab file
# 4. Fill in release notes
# 5. Save and review
```

### Step 5: Release Management

**Internal Testing:**
- Upload to Internal Testing track
- Add tester emails
- Instant availability

**Closed Testing:**
- Upload to Closed Testing track
- Add specific testers
- Review required (few hours)

**Open Testing:**
- Upload to Open Testing track
- Anyone can join
- Review required (1-2 days)

**Production:**
- Promote from testing or direct upload
- Review required (1-7 days)
- Staged rollout recommended (1%, 5%, 20%, 50%, 100%)

---

## Post-Deployment

### 1. Monitor App Performance

**App Store Connect (iOS):**
- Crashes and performance
- App analytics
- Sales and trends
- Customer reviews

**Google Play Console (Android):**
- Android Vitals
- User acquisition
- Revenue
- Reviews

### 2. Setup Crash Reporting

**Sentry Integration:**
```bash
npm install @sentry/react-native
```

Configure in `app.json`:
```json
{
  "expo": {
    "extra": {
      "sentry": {
        "organization": "your-org",
        "project": "saga-pos"
      }
    }
  }
}
```

### 3. Monitor Push Notifications

**Firebase Console:**
- Delivery metrics
- Engagement metrics
- Conversion tracking

### 4. Update App Version

**Version Numbering:**
```
Major.Minor.Patch
2.1.0 → 2.1.1 (bug fix)
2.1.0 → 2.2.0 (new features)
2.1.0 → 3.0.0 (major changes)
```

**Update Process:**
1. Update version in `app.json`
2. Update build number (iOS) / version code (Android)
3. Build new version
4. Submit to stores
5. Monitor rollout

---

## Monitoring & Maintenance

### Daily Tasks
- [ ] Check crash reports
- [ ] Monitor app ratings
- [ ] Respond to user reviews
- [ ] Check push notification delivery

### Weekly Tasks
- [ ] Review analytics
- [ ] Check API error rates
- [ ] Monitor user engagement
- [ ] Review support tickets

### Monthly Tasks
- [ ] Update dependencies
- [ ] Security audit
- [ ] Performance optimization
- [ ] Plan next release

---

## Troubleshooting

### Build Fails

**iOS:**
```bash
# Clean build cache
eas build:cancel --id=BUILD_ID
eas build --platform ios --clear-cache

# Check certificates
eas credentials --platform ios
```

**Android:**
```bash
# Clean build cache
eas build --platform android --clear-cache

# Check keystore
keytool -list -v -keystore saga-pos.keystore -alias saga-pos
```

### App Rejected

**Common Reasons:**
1. **Privacy Policy Missing:** Add URL in app store connect
2. **Broken Features:** Test all flows before submission
3. **Metadata Issues:** Ensure screenshots match app
4. **Guideline Violations:** Review App Store Guidelines

**Appeal Process:**
1. Read rejection reason carefully
2. Fix the issue
3. Resubmit with explanation
4. Contact reviewer if unclear

### Push Notifications Not Working

**Checklist:**
- [ ] Firebase project configured
- [ ] Push token generated
- [ ] Device registered
- [ ] Permissions granted
- [ ] Backend sending to correct token

**Debug:**
```typescript
const token = await Notifications.getExpoPushTokenAsync();
console.log('Push token:', token);
```

---

## App Store Optimization (ASO)

### Keywords Strategy
**Primary Keywords:**
- shopping app
- pos system
- retail app
- loyalty program

**Long-tail Keywords:**
- online shopping indonesia
- pos mobile app
- retail management
- customer loyalty

### Screenshots Best Practices
1. Show key features
2. Use benefit-focused captions
3. Include social proof
4. Show app in use
5. Highlight unique features

### Description Tips
1. First 3 lines are critical (show in preview)
2. Include keywords naturally
3. Highlight benefits not features
4. Include call-to-action
5. Update regularly with new features

---

## Release Timeline

### Week 1: Preparation
- Day 1-2: Final testing
- Day 3-4: Prepare store listings
- Day 5: Create marketing materials

### Week 2: Submission
- Day 1: Submit to iOS App Store
- Day 2: Submit to Google Play
- Day 3-5: Address review feedback

### Week 3: Launch
- Day 1: iOS approval
- Day 2: Android approval
- Day 3: Coordinated launch
- Day 4-5: Monitor and respond

### Week 4: Post-Launch
- Monitor crashes
- Respond to reviews
- Gather feedback
- Plan updates

---

## Support & Resources

### Expo Documentation
- https://docs.expo.dev

### App Store Guidelines
- iOS: https://developer.apple.com/app-store/review/guidelines/
- Android: https://play.google.com/about/developer-content-policy/

### Contact
- Technical Support: tech-support@sagaposo.com
- App Store Issues: appstore@sagaposo.com

---

*Deployment Guide v2.1.0 - SAGA POS Mobile App*
