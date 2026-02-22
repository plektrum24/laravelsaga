import React, { useEffect, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  ActivityIndicator,
  FlatList,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useAuth } from '../../hooks/useAuth';
import { loyaltyService } from '../../services/loyalty.service';
import { LoyaltyPoints, CustomerTier, Reward } from '../../types/api.types';
import { router } from 'expo-router';

export default function LoyaltyScreen() {
  const { isAuthenticated, user } = useAuth();
  const [points, setPoints] = useState<LoyaltyPoints | null>(null);
  const [tier, setTier] = useState<CustomerTier | null>(null);
  const [rewards, setRewards] = useState<Reward[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    if (isAuthenticated) {
      loadLoyaltyData();
    }
  }, [isAuthenticated]);

  const loadLoyaltyData = async () => {
    setIsLoading(true);
    try {
      const [pointsData, tierData, rewardsData] = await Promise.all([
        loyaltyService.getPoints(),
        loyaltyService.getTier(),
        loyaltyService.getRewards({ limit: 10, status: 'active' }),
      ]);
      setPoints(pointsData);
      setTier(tierData);
      setRewards(rewardsData.data);
    } catch (error) {
      console.error('Failed to load loyalty data:', error);
    } finally {
      setIsLoading(false);
    }
  };

  const handleRedeemReward = async (reward: Reward) => {
    if (!isAuthenticated) {
      router.push('/(auth)/login');
      return;
    }

    if (points && points.points < reward.points_required) {
      alert('Insufficient points');
      return;
    }

    try {
      await loyaltyService.redeemReward(reward.id);
      alert('Reward redeemed successfully!');
      loadLoyaltyData();
    } catch (error: any) {
      alert(error.message || 'Failed to redeem reward');
    }
  };

  if (!isAuthenticated) {
    return (
      <View style={styles.notLoggedInContainer}>
        <Ionicons name="gift-outline" size={64} color="#9CA3AF" />
        <Text style={styles.notLoggedInTitle}>Sign In to View Rewards</Text>
        <Text style={styles.notLoggedInSubtitle}>
          Earn points on every purchase and redeem them for exclusive rewards
        </Text>
        <TouchableOpacity
          style={styles.loginButton}
          onPress={() => router.push('/(auth)/login')}
        >
          <Text style={styles.loginButtonText}>Sign In</Text>
        </TouchableOpacity>
      </View>
    );
  }

  if (isLoading) {
    return (
      <View style={styles.loadingContainer}>
        <ActivityIndicator size="large" color="#4F46E5" />
        <Text style={styles.loadingText}>Loading rewards...</Text>
      </View>
    );
  }

  return (
    <ScrollView style={styles.container}>
      {/* Points & Tier Card */}
      <View style={styles.pointsCard}>
        <View style={styles.pointsHeader}>
          <View>
            <Text style={styles.pointsLabel}>Your Points</Text>
            <Text style={styles.pointsValue}>
              {points?.points.toLocaleString('id-ID') || '0'}
            </Text>
            <Text style={styles.pointsSubtext}>
              Lifetime: {points?.lifetime_points.toLocaleString('id-ID') || '0'} pts
            </Text>
          </View>
          <View style={styles.tierBadge}>
            <Ionicons name="trophy" size={24} color="#F59E0B" />
            <Text style={styles.tierName}>
              {tier?.tier?.name || 'Member'}
            </Text>
          </View>
        </View>
        
        {tier && tier.points_to_next_tier !== undefined && (
          <View style={styles.progressContainer}>
            <View style={styles.progressHeader}>
              <Text style={styles.progressText}>
                {tier.points} / {tier.tier.max_points || tier.points + tier.points_to_next_tier} pts
              </Text>
              <Text style={styles.progressRemaining}>
                {tier.points_to_next_tier} pts to next tier
              </Text>
            </View>
            <View style={styles.progressBar}>
              <View
                style={[
                  styles.progressFill,
                  { width: `${Math.min((tier.points / (tier.tier.max_points || tier.points + tier.points_to_next_tier)) * 100, 100)}%` },
                ]}
              />
            </View>
          </View>
        )}
      </View>

      {/* QR Membership Card */}
      <TouchableOpacity style={styles.qrCard} onPress={() => {/* Show QR fullscreen */}}>
        <View style={styles.qrContent}>
          <View style={styles.qrCode}>
            <Ionicons name="qr-code" size={80} color="#111827" />
          </View>
          <Text style={styles.qrLabel}>Membership Card</Text>
          <Text style={styles.qrSubLabel}>Tap to view</Text>
        </View>
      </TouchableOpacity>

      {/* How to Earn */}
      <View style={styles.section}>
        <Text style={styles.sectionTitle}>How to Earn Points</Text>
        <View style={styles.earnGrid}>
          <View style={styles.earnCard}>
            <View style={[styles.earnIcon, { backgroundColor: '#EEF2FF' }]}>
              <Ionicons name="cart" size={24} color="#4F46E5" />
            </View>
            <Text style={styles.earnLabel}>Shop</Text>
            <Text style={styles.earnValue}>1 pt per Rp 10,000</Text>
          </View>
          <View style={styles.earnCard}>
            <View style={[styles.earnIcon, { backgroundColor: '#FEF2F2' }]}>
              <Ionicons name="star" size={24} color="#EF4444" />
            </View>
            <Text style={styles.earnLabel}>Review</Text>
            <Text style={styles.earnValue}>50 pts per review</Text>
          </View>
          <View style={styles.earnCard}>
            <View style={[styles.earnIcon, { backgroundColor: '#ECFDF5' }]}>
              <Ionicons name="people" size={24} color="#10B981" />
            </View>
            <Text style={styles.earnLabel}>Refer</Text>
            <Text style={styles.earnValue}>500 pts per friend</Text>
          </View>
        </View>
      </View>

      {/* Rewards Catalog */}
      <View style={styles.section}>
        <View style={styles.sectionHeader}>
          <Text style={styles.sectionTitle}>Available Rewards</Text>
          <TouchableOpacity>
            <Text style={styles.seeAll}>See All</Text>
          </TouchableOpacity>
        </View>
        
        {rewards.length > 0 ? (
          rewards.map((reward) => (
            <TouchableOpacity
              key={reward.id}
              style={styles.rewardCard}
              onPress={() => handleRedeemReward(reward)}
            >
              <View style={styles.rewardImageContainer}>
                {reward.image ? (
                  <View style={styles.rewardImage}>
                    <Ionicons name="image" size={40} color="#9CA3AF" />
                  </View>
                ) : (
                  <View style={styles.rewardImage}>
                    <Ionicons name="gift" size={40} color="#9CA3AF" />
                  </View>
                )}
              </View>
              <View style={styles.rewardInfo}>
                <Text style={styles.rewardName} numberOfLines={2}>
                  {reward.name}
                </Text>
                <View style={styles.rewardPoints}>
                  <Ionicons name="star" size={16} color="#F59E0B" />
                  <Text style={styles.rewardPointsValue}>
                    {reward.points_required.toLocaleString('id-ID')} pts
                  </Text>
                </View>
                <Text style={styles.rewardDescription} numberOfLines={2}>
                  {reward.description}
                </Text>
              </View>
              <TouchableOpacity
                style={[
                  styles.redeemBtn,
                  points && points.points < reward.points_required && styles.redeemBtnDisabled,
                ]}
                onPress={() => handleRedeemReward(reward)}
              >
                <Text style={styles.redeemBtnText}>Redeem</Text>
              </TouchableOpacity>
            </TouchableOpacity>
          ))
        ) : (
          <View style={styles.emptyRewards}>
            <Ionicons name="gift-outline" size={48} color="#9CA3AF" />
            <Text style={styles.emptyRewardsText}>No rewards available</Text>
          </View>
        )}
      </View>

      <View style={{ height: 100 }} />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  notLoggedInContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    padding: 24,
  },
  notLoggedInTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
    marginTop: 16,
    marginBottom: 8,
  },
  notLoggedInSubtitle: {
    fontSize: 14,
    color: '#6B7280',
    textAlign: 'center',
    marginBottom: 24,
  },
  loginButton: {
    backgroundColor: '#4F46E5',
    paddingHorizontal: 32,
    paddingVertical: 14,
    borderRadius: 12,
  },
  loginButtonText: {
    color: '#FFFFFF',
    fontSize: 16,
    fontWeight: '600',
  },
  loadingContainer: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  loadingText: {
    marginTop: 16,
    color: '#9CA3AF',
    fontSize: 14,
  },
  pointsCard: {
    margin: 16,
    padding: 20,
    backgroundColor: 'linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%)',
    borderRadius: 16,
    ...Platform.select({
      web: {
        background: 'linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%)',
      },
    }),
  },
  pointsHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 16,
  },
  pointsLabel: {
    fontSize: 14,
    color: '#E0E7FF',
    marginBottom: 4,
  },
  pointsValue: {
    fontSize: 36,
    fontWeight: 'bold',
    color: '#FFFFFF',
  },
  pointsSubtext: {
    fontSize: 12,
    color: '#C7D2FE',
    marginTop: 4,
  },
  tierBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    backgroundColor: 'rgba(255,255,255,0.2)',
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 20,
    gap: 6,
  },
  tierName: {
    color: '#FFFFFF',
    fontSize: 14,
    fontWeight: '600',
  },
  progressContainer: {
    marginTop: 8,
  },
  progressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  progressText: {
    fontSize: 12,
    color: '#E0E7FF',
  },
  progressRemaining: {
    fontSize: 12,
    color: '#F59E0B',
    fontWeight: '500',
  },
  progressBar: {
    height: 8,
    backgroundColor: 'rgba(255,255,255,0.2)',
    borderRadius: 4,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#F59E0B',
    borderRadius: 4,
  },
  qrCard: {
    marginHorizontal: 16,
    marginBottom: 16,
    backgroundColor: '#FFFFFF',
    borderRadius: 16,
    padding: 20,
    shadowColor: '#000',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.1,
    shadowRadius: 4,
    elevation: 3,
  },
  qrContent: {
    alignItems: 'center',
  },
  qrCode: {
    backgroundColor: '#F3F4F6',
    padding: 16,
    borderRadius: 12,
    marginBottom: 12,
  },
  qrLabel: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  qrSubLabel: {
    fontSize: 14,
    color: '#6B7280',
    marginTop: 4,
  },
  section: {
    marginTop: 8,
    backgroundColor: '#FFFFFF',
    paddingBottom: 16,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
    paddingHorizontal: 16,
    paddingTop: 16,
    marginBottom: 12,
  },
  sectionHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    paddingHorizontal: 16,
    marginBottom: 12,
  },
  seeAll: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '500',
  },
  earnGrid: {
    flexDirection: 'row',
    paddingHorizontal: 16,
    gap: 12,
  },
  earnCard: {
    flex: 1,
    alignItems: 'center',
    padding: 12,
    backgroundColor: '#F9FAFB',
    borderRadius: 12,
  },
  earnIcon: {
    width: 56,
    height: 56,
    borderRadius: 28,
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  earnLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 4,
  },
  earnValue: {
    fontSize: 11,
    color: '#6B7280',
    textAlign: 'center',
  },
  rewardCard: {
    flexDirection: 'row',
    marginHorizontal: 16,
    marginBottom: 12,
    backgroundColor: '#F9FAFB',
    borderRadius: 12,
    padding: 12,
    alignItems: 'center',
  },
  rewardImageContainer: {
    width: 60,
    height: 60,
    borderRadius: 8,
    backgroundColor: '#FFFFFF',
    overflow: 'hidden',
  },
  rewardImage: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
  },
  rewardInfo: {
    flex: 1,
    marginLeft: 12,
  },
  rewardName: {
    fontSize: 14,
    fontWeight: '500',
    color: '#111827',
    marginBottom: 4,
  },
  rewardPoints: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 4,
    marginBottom: 4,
  },
  rewardPointsValue: {
    fontSize: 13,
    fontWeight: '600',
    color: '#F59E0B',
  },
  rewardDescription: {
    fontSize: 12,
    color: '#6B7280',
  },
  redeemBtn: {
    backgroundColor: '#4F46E5',
    paddingHorizontal: 16,
    paddingVertical: 8,
    borderRadius: 8,
  },
  redeemBtnDisabled: {
    backgroundColor: '#9CA3AF',
  },
  redeemBtnText: {
    color: '#FFFFFF',
    fontSize: 13,
    fontWeight: '600',
  },
  emptyRewards: {
    padding: 40,
    alignItems: 'center',
  },
  emptyRewardsText: {
    color: '#9CA3AF',
    fontSize: 14,
    marginTop: 8,
  },
});
