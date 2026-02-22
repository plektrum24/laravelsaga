import React from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface LoyaltyDashboardProps {
  points?: {
    balance: number;
    lifetime: number;
    expiring: number;
    expiryDate?: string;
  };
  tier?: {
    name: string;
    level: number;
    color: string;
    benefits: string[];
    nextTier?: string;
    progressToNext: number;
  };
  recentActivity?: Array<{
    id: string;
    type: 'earn' | 'redeem' | 'expire';
    points: number;
    description: string;
    date: string;
  }>;
  availableRewards?: Array<{
    id: string;
    name: string;
    pointsRequired: number;
    description?: string;
  }>;
  onViewHistory?: () => void;
  onViewRewards?: () => void;
  onRedeemPoints?: () => void;
}

export default function LoyaltyDashboard({
  points = { balance: 0, lifetime: 0, expiring: 0 },
  tier = {
    name: 'Member',
    level: 1,
    color: '#6B7280',
    benefits: [],
    progressToNext: 0,
  },
  recentActivity = [],
  availableRewards = [],
  onViewHistory,
  onViewRewards,
  onRedeemPoints,
}: LoyaltyDashboardProps) {
  const formatPoints = (amount: number) => {
    return amount.toLocaleString('id-ID');
  };

  const getTierIcon = (level: number) => {
    if (level >= 3) return 'diamond';
    if (level >= 2) return 'star';
    return 'person';
  };

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Points Overview */}
      <View style={styles.pointsCard}>
        <View style={styles.pointsHeader}>
          <Text style={styles.pointsLabel}>Available Points</Text>
          <TouchableOpacity onPress={onRedeemPoints}>
            <Text style={styles.redeemLink}>Redeem</Text>
          </TouchableOpacity>
        </View>
        <Text style={styles.pointsBalance}>{formatPoints(points.balance)}</Text>
        <View style={styles.pointsStats}>
          <View style={styles.statItem}>
            <Ionicons name="arrow-up-circle" size={16} color="#10B981" />
            <Text style={styles.statText}>
              Lifetime: {formatPoints(points.lifetime)}
            </Text>
          </View>
          {points.expiring > 0 && (
            <View style={styles.statItem}>
              <Ionicons name="warning" size={16} color="#F59E0B" />
              <Text style={styles.statTextWarning}>
                {formatPoints(points.expiring)} expiring{' '}
                {points.expiryDate || 'soon'}
              </Text>
            </View>
          )}
        </View>
      </View>

      {/* Tier Status */}
      <View style={styles.tierCard}>
        <View style={styles.tierHeader}>
          <View
            style={[
              styles.tierIcon,
              { backgroundColor: `${tier.color}15` },
            ]}
          >
            <Ionicons
              name={getTierIcon(tier.level) as any}
              size={32}
              color={tier.color}
            />
          </View>
          <View style={styles.tierInfo}>
            <Text style={styles.tierName}>{tier.name}</Text>
            <Text style={styles.tierLevel}>Level {tier.level}</Text>
          </View>
        </View>

        {tier.nextTier && (
          <View style={styles.progressContainer}>
            <View style={styles.progressHeader}>
              <Text style={styles.progressLabel}>Progress to {tier.nextTier}</Text>
              <Text style={styles.progressPercent}>
                {Math.round(tier.progressToNext)}%
              </Text>
            </View>
            <View style={styles.progressBar}>
              <View
                style={[
                  styles.progressFill,
                  {
                    width: `${Math.min(tier.progressToNext, 100)}%`,
                    backgroundColor: tier.color,
                  },
                ]}
              />
            </View>
          </View>
        )}

        {/* Tier Benefits */}
        {tier.benefits.length > 0 && (
          <View style={styles.benefitsContainer}>
            <Text style={styles.benefitsTitle}>Your Benefits</Text>
            {tier.benefits.map((benefit, index) => (
              <View key={index} style={styles.benefitItem}>
                <Ionicons name="checkmark-circle" size={16} color={tier.color} />
                <Text style={styles.benefitText}>{benefit}</Text>
              </View>
            ))}
          </View>
        )}
      </View>

      {/* Quick Actions */}
      <View style={styles.actionsContainer}>
        <TouchableOpacity style={styles.actionButton} onPress={onViewHistory}>
          <Ionicons name="time" size={24} color="#4F46E5" />
          <Text style={styles.actionText}>View History</Text>
        </TouchableOpacity>
        <TouchableOpacity style={styles.actionButton} onPress={onViewRewards}>
          <Ionicons name="gift" size={24} color="#4F46E5" />
          <Text style={styles.actionText}>Rewards</Text>
        </TouchableOpacity>
      </View>

      {/* Recent Activity */}
      {recentActivity.length > 0 && (
        <View style={styles.activityContainer}>
          <View style={styles.activityHeader}>
            <Text style={styles.activityTitle}>Recent Activity</Text>
            <TouchableOpacity onPress={onViewHistory}>
              <Text style={styles.seeAll}>See All</Text>
            </TouchableOpacity>
          </View>
          {recentActivity.slice(0, 5).map((activity) => (
            <View key={activity.id} style={styles.activityItem}>
              <View
                style={[
                  styles.activityIcon,
                  {
                    backgroundColor:
                      activity.type === 'earn'
                        ? '#ECFDF5'
                        : activity.type === 'redeem'
                        ? '#FEF2F2'
                        : '#FEF3C7',
                  },
                ]}
              >
                <Ionicons
                  name={
                    activity.type === 'earn'
                      ? 'arrow-down'
                      : activity.type === 'redeem'
                      ? 'arrow-up'
                      : 'time'
                  }
                  size={16}
                  color={
                    activity.type === 'earn'
                      ? '#10B981'
                      : activity.type === 'redeem'
                      ? '#EF4444'
                      : '#F59E0B'
                  }
                />
              </View>
              <View style={styles.activityInfo}>
                <Text style={styles.activityDescription}>
                  {activity.description}
                </Text>
                <Text style={styles.activityDate}>
                  {new Date(activity.date).toLocaleDateString('id-ID')}
                </Text>
              </View>
              <Text
                style={[
                  styles.activityPoints,
                  {
                    color:
                      activity.type === 'earn'
                        ? '#10B981'
                        : activity.type === 'redeem'
                        ? '#EF4444'
                        : '#F59E0B',
                  },
                ]}
              >
                {activity.type === 'earn' ? '+' : '-'}
                {formatPoints(Math.abs(activity.points))}
              </Text>
            </View>
          ))}
        </View>
      )}

      {/* Available Rewards Preview */}
      {availableRewards.length > 0 && (
        <View style={styles.rewardsContainer}>
          <View style={styles.rewardsHeader}>
            <Text style={styles.rewardsTitle}>Available Rewards</Text>
            <TouchableOpacity onPress={onViewRewards}>
              <Text style={styles.seeAll}>See All</Text>
            </TouchableOpacity>
          </View>
          <ScrollView horizontal showsHorizontalScrollIndicator={false}>
            {availableRewards.slice(0, 3).map((reward) => (
              <View key={reward.id} style={styles.rewardCard}>
                <View style={styles.rewardIconContainer}>
                  <Ionicons name="gift" size={32} color="#F59E0B" />
                </View>
                <Text style={styles.rewardName} numberOfLines={1}>
                  {reward.name}
                </Text>
                <Text style={styles.rewardPoints}>
                  {formatPoints(reward.pointsRequired)} pts
                </Text>
              </View>
            ))}
          </ScrollView>
        </View>
      )}

      <View style={{ height: 40 }} />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  pointsCard: {
    backgroundColor: '#4F46E5',
    margin: 16,
    padding: 20,
    borderRadius: 16,
    shadowColor: '#4F46E5',
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.3,
    shadowRadius: 8,
    elevation: 4,
  },
  pointsHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  pointsLabel: {
    fontSize: 14,
    color: '#FFFFFF',
    opacity: 0.9,
  },
  redeemLink: {
    fontSize: 14,
    color: '#FFFFFF',
    fontWeight: '600',
  },
  pointsBalance: {
    fontSize: 36,
    fontWeight: 'bold',
    color: '#FFFFFF',
    marginBottom: 16,
  },
  pointsStats: {
    gap: 8,
  },
  statItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
  },
  statText: {
    fontSize: 13,
    color: '#FFFFFF',
    opacity: 0.9,
  },
  statTextWarning: {
    fontSize: 13,
    color: '#FCD34D',
  },
  tierCard: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 16,
  },
  tierHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 16,
  },
  tierIcon: {
    width: 64,
    height: 64,
    borderRadius: 32,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 16,
  },
  tierInfo: {
    flex: 1,
  },
  tierName: {
    fontSize: 20,
    fontWeight: 'bold',
    color: '#111827',
    marginBottom: 4,
  },
  tierLevel: {
    fontSize: 14,
    color: '#6B7280',
  },
  progressContainer: {
    marginBottom: 16,
  },
  progressHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 8,
  },
  progressLabel: {
    fontSize: 14,
    color: '#6B7280',
  },
  progressPercent: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  progressBar: {
    height: 8,
    backgroundColor: '#E5E7EB',
    borderRadius: 4,
    overflow: 'hidden',
  },
  progressFill: {
    height: '100%',
    borderRadius: 4,
  },
  benefitsContainer: {
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
    paddingTop: 16,
  },
  benefitsTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 12,
  },
  benefitItem: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    marginBottom: 8,
  },
  benefitText: {
    fontSize: 13,
    color: '#6B7280',
  },
  actionsContainer: {
    flexDirection: 'row',
    gap: 12,
    marginHorizontal: 16,
    marginBottom: 16,
  },
  actionButton: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#FFFFFF',
    paddingVertical: 14,
    borderRadius: 12,
    gap: 8,
    borderWidth: 1,
    borderColor: '#E5E7EB',
  },
  actionText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#4F46E5',
  },
  activityContainer: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 16,
  },
  activityHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  activityTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  seeAll: {
    fontSize: 14,
    color: '#4F46E5',
    fontWeight: '500',
  },
  activityItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
  },
  activityIcon: {
    width: 36,
    height: 36,
    borderRadius: 18,
    justifyContent: 'center',
    alignItems: 'center',
    marginRight: 12,
  },
  activityInfo: {
    flex: 1,
  },
  activityDescription: {
    fontSize: 14,
    color: '#111827',
    fontWeight: '500',
    marginBottom: 2,
  },
  activityDate: {
    fontSize: 12,
    color: '#9CA3AF',
  },
  activityPoints: {
    fontSize: 14,
    fontWeight: '600',
  },
  rewardsContainer: {
    backgroundColor: '#FFFFFF',
    marginHorizontal: 16,
    marginBottom: 16,
    padding: 16,
    borderRadius: 16,
  },
  rewardsHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    marginBottom: 12,
  },
  rewardsTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  rewardCard: {
    width: 140,
    marginRight: 12,
    padding: 12,
    backgroundColor: '#F9FAFB',
    borderRadius: 12,
    alignItems: 'center',
  },
  rewardIconContainer: {
    width: 64,
    height: 64,
    borderRadius: 32,
    backgroundColor: '#FEF3C7',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 8,
  },
  rewardName: {
    fontSize: 13,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 4,
  },
  rewardPoints: {
    fontSize: 12,
    color: '#F59E0B',
    fontWeight: '600',
  },
});
