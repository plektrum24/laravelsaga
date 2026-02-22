import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  TouchableOpacity,
  Share,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface QRMembershipCardProps {
  memberName: string;
  memberSince: string;
  tierName: string;
  tierColor: string;
  memberId: string;
  qrCodeValue?: string;
  pointsBalance?: number;
  onShareCard?: () => void;
}

export default function QRMembershipCard({
  memberName,
  memberSince,
  tierName,
  tierColor,
  memberId,
  qrCodeValue = memberId,
  pointsBalance,
  onShareCard,
}: QRMembershipCardProps) {
  const handleShare = async () => {
    if (onShareCard) {
      onShareCard();
    } else {
      try {
        await Share.share({
          message: `Join me at SAGA POS! My member ID: ${memberId}`,
          title: 'SAGA POS Membership',
        });
      } catch (error) {
        console.error('Share error:', error);
      }
    }
  };

  return (
    <View style={styles.container}>
      {/* Membership Card */}
      <View
        style={[
          styles.card,
          {
            background: `linear-gradient(135deg, ${tierColor} 0%, ${tierColor}DD 100%)`,
          },
        ]}
      >
        {/* Card Header */}
        <View style={styles.cardHeader}>
          <View style={styles.logo}>
            <Ionicons name="star" size={24} color="#FFFFFF" />
            <Text style={styles.logoText}>SAGA POS</Text>
          </View>
          <TouchableOpacity onPress={handleShare}>
            <Ionicons name="share-outline" size={24} color="#FFFFFF" />
          </TouchableOpacity>
        </View>

        {/* Tier Badge */}
        <View style={styles.tierBadge}>
          <Ionicons name="diamond" size={32} color="#FFFFFF" />
          <Text style={styles.tierName}>{tierName}</Text>
        </View>

        {/* Member Info */}
        <View style={styles.memberInfo}>
          <Text style={styles.memberName}>{memberName}</Text>
          <Text style={styles.memberSince}>Member since {memberSince}</Text>
          <Text style={styles.memberId}>ID: {memberId}</Text>
        </View>

        {/* QR Code Placeholder */}
        <View style={styles.qrContainer}>
          <View style={styles.qrCode}>
            {/* In production, use react-native-qrcode-svg or similar */}
            <Ionicons name="qr-code" size={120} color="#FFFFFF" />
          </View>
          <Text style={styles.qrHint}>Scan at checkout</Text>
        </View>

        {/* Points Balance */}
        {pointsBalance !== undefined && (
          <View style={styles.pointsContainer}>
            <Ionicons name="ticket" size={20} color="#FFFFFF" />
            <Text style={styles.pointsBalance}>{pointsBalance.toLocaleString('id-ID')} pts</Text>
          </View>
        )}

        {/* Card Number */}
        <View style={styles.cardNumber}>
          <Text style={styles.cardNumberLabel}>Membership Number</Text>
          <Text style={styles.cardNumberValue}>{memberId}</Text>
        </View>
      </View>

      {/* How to Use */}
      <View style={styles.howToUseContainer}>
        <View style={styles.howToUseHeader}>
          <Ionicons name="information-circle" size={20} color="#4F46E5" />
          <Text style={styles.howToUseTitle}>How to Use</Text>
        </View>
        <View style={styles.stepContainer}>
          <View style={styles.stepNumber}>
            <Text style={styles.stepNumberText}>1</Text>
          </View>
          <Text style={styles.stepText}>Show this card at checkout</Text>
        </View>
        <View style={styles.stepContainer}>
          <View style={styles.stepNumber}>
            <Text style={styles.stepNumberText}>2</Text>
          </View>
          <Text style={styles.stepText}>Cashier will scan the QR code</Text>
        </View>
        <View style={styles.stepContainer}>
          <View style={styles.stepNumber}>
            <Text style={styles.stepNumberText}>3</Text>
          </View>
          <Text style={styles.stepText}>Earn points on your purchase</Text>
        </View>
      </View>

      {/* Benefits */}
      <View style={styles.benefitsContainer}>
        <Text style={styles.benefitsTitle}>Member Benefits</Text>
        <View style={styles.benefitsGrid}>
          <View style={styles.benefitItem}>
            <Ionicons name="pricetag" size={20} color="#10B981" />
            <Text style={styles.benefitText}>Earn points on every purchase</Text>
          </View>
          <View style={styles.benefitItem}>
            <Ionicons name="gift" size={20} color="#10B981" />
            <Text style={styles.benefitText}>Redeem points for rewards</Text>
          </View>
          <View style={styles.benefitItem}>
            <Ionicons name="star" size={20} color="#10B981" />
            <Text style={styles.benefitText}>Exclusive member discounts</Text>
          </View>
          <View style={styles.benefitItem}>
            <Ionicons name="cake" size={20} color="#10B981" />
            <Text style={styles.benefitText}>Birthday rewards</Text>
          </View>
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  card: {
    margin: 16,
    borderRadius: 20,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 8,
  },
  cardHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 20,
  },
  logo: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
  },
  logoText: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#FFFFFF',
  },
  tierBadge: {
    alignItems: 'center',
    marginBottom: 20,
  },
  tierName: {
    fontSize: 24,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginTop: 8,
  },
  memberInfo: {
    alignItems: 'center',
    marginBottom: 20,
  },
  memberName: {
    fontSize: 22,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginBottom: 4,
  },
  memberSince: {
    fontSize: 14,
    color: '#FFFFFF',
    opacity: 0.9,
    marginBottom: 4,
  },
  memberId: {
    fontSize: 12,
    color: '#FFFFFF',
    opacity: 0.8,
  },
  qrContainer: {
    alignItems: 'center',
    marginBottom: 20,
  },
  qrCode: {
    backgroundColor: '#FFFFFF',
    padding: 16,
    borderRadius: 16,
    marginBottom: 8,
  },
  qrHint: {
    fontSize: 12,
    color: '#FFFFFF',
    opacity: 0.9,
  },
  pointsContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
    marginBottom: 20,
  },
  pointsBalance: {
    fontSize: 18,
    fontWeight: 'bold',
    color: '#FFFFFF',
  },
  cardNumber: {
    borderTopWidth: 1,
    borderTopColor: '#FFFFFF',
    borderTopOpacity: 0.3,
    paddingTop: 16,
    alignItems: 'center',
  },
  cardNumberLabel: {
    fontSize: 12,
    color: '#FFFFFF',
    opacity: 0.8,
    marginBottom: 4,
  },
  cardNumberValue: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFFFFF',
    letterSpacing: 2,
  },
  howToUseContainer: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 16,
  },
  howToUseHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 12,
  },
  howToUseTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  stepContainer: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    marginBottom: 12,
  },
  stepNumber: {
    width: 28,
    height: 28,
    borderRadius: 14,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
  },
  stepNumberText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#4F46E5',
  },
  stepText: {
    fontSize: 14,
    color: '#6B7280',
    flex: 1,
  },
  benefitsContainer: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 16,
  },
  benefitsTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 12,
  },
  benefitsGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 12,
  },
  benefitItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    width: '48%',
  },
  benefitText: {
    fontSize: 13,
    color: '#6B7280',
    flex: 1,
  },
});
