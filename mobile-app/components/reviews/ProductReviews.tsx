import React, { useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  ScrollView,
  TouchableOpacity,
  TextInput,
  Image,
  Modal,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';

interface Review {
  id: string;
  user_name: string;
  rating: number;
  comment: string;
  created_at: string;
  helpful_count?: number;
  images?: string[];
}

interface ProductReviewsProps {
  productId: string;
  reviews?: Review[];
  averageRating?: number;
  totalReviews?: number;
  onAddReview?: (rating: number, comment: string) => void;
  onHelpful?: (reviewId: string) => void;
}

export default function ProductReviews({
  productId,
  reviews = [],
  averageRating = 0,
  totalReviews = 0,
  onAddReview,
  onHelpful,
}: ProductReviewsProps) {
  const [showAddReview, setShowAddReview] = useState(false);
  const [rating, setRating] = useState(0);
  const [comment, setComment] = useState('');

  const handleSubmitReview = () => {
    if (rating === 0) return;
    if (onAddReview) {
      onAddReview(rating, comment);
    }
    setRating(0);
    setComment('');
    setShowAddReview(false);
  };

  const renderStars = (rating: number, size: number = 16) => {
    return (
      <View style={styles.stars}>
        {[1, 2, 3, 4, 5].map((star) => (
          <Ionicons
            key={star}
            name={star <= rating ? 'star' : 'star-outline'}
            size={size}
            color="#F59E0B"
          />
        ))}
      </View>
    );
  };

  const ratingDistribution = [5, 4, 3, 2, 1].map((stars) => {
    const count = reviews.filter((r) => r.rating === stars).length;
    const percentage = totalReviews > 0 ? (count / totalReviews) * 100 : 0;
    return { stars, count, percentage };
  });

  return (
    <ScrollView style={styles.container} showsVerticalScrollIndicator={false}>
      {/* Rating Summary */}
      <View style={styles.summaryContainer}>
        <View style={styles.summaryLeft}>
          <Text style={styles.averageRating}>{averageRating.toFixed(1)}</Text>
          {renderStars(Math.round(averageRating), 24)}
          <Text style={styles.totalReviews}>{totalReviews} reviews</Text>
        </View>

        <View style={styles.summaryRight}>
          {ratingDistribution.map(({ stars, percentage }) => (
            <View key={stars} style={styles.ratingRow}>
              <Text style={styles.ratingStar}>{stars}</Text>
              <Ionicons name="star" size={12} color="#F59E0B" />
              <View style={styles.ratingBar}>
                <View
                  style={[
                    styles.ratingFill,
                    { width: `${percentage}%` },
                  ]}
                />
              </View>
              <Text style={styles.ratingCount}>{percentage.toFixed(0)}%</Text>
            </View>
          ))}
        </View>
      </View>

      {/* Write Review Button */}
      <TouchableOpacity
        style={styles.writeReviewButton}
        onPress={() => setShowAddReview(true)}
      >
        <Ionicons name="create-outline" size={20} color="#4F46E5" />
        <Text style={styles.writeReviewText}>Write a Review</Text>
      </TouchableOpacity>

      {/* Reviews List */}
      <View style={styles.reviewsContainer}>
        <Text style={styles.reviewsTitle}>Customer Reviews</Text>

        {reviews.length === 0 ? (
          <View style={styles.noReviews}>
            <Ionicons name="chatbubble-ellipses-outline" size={48} color="#D1D5DB" />
            <Text style={styles.noReviewsText}>No reviews yet</Text>
            <Text style={styles.noReviewsSubtitle}>
              Be the first to review this product
            </Text>
          </View>
        ) : (
          reviews.map((review) => (
            <View key={review.id} style={styles.reviewCard}>
              <View style={styles.reviewHeader}>
                <View style={styles.reviewUser}>
                  <View style={styles.userAvatar}>
                    <Text style={styles.userAvatarText}>
                      {review.user_name.charAt(0).toUpperCase()}
                    </Text>
                  </View>
                  <View>
                    <Text style={styles.userName}>{review.user_name}</Text>
                    <Text style={styles.reviewDate}>
                      {new Date(review.created_at).toLocaleDateString('id-ID')}
                    </Text>
                  </View>
                </View>
                {renderStars(review.rating)}
              </View>

              <Text style={styles.reviewComment}>{review.comment}</Text>

              {review.images && review.images.length > 0 && (
                <View style={styles.reviewImages}>
                  {review.images.map((image, index) => (
                    <Image
                      key={index}
                      source={{ uri: image }}
                      style={styles.reviewImage}
                    />
                  ))}
                </View>
              )}

              <View style={styles.reviewFooter}>
                <TouchableOpacity
                  style={styles.helpfulButton}
                  onPress={() => onHelpful?.(review.id)}
                >
                  <Ionicons name="thumbs-up-outline" size={16} color="#6B7280" />
                  <Text style={styles.helpfulText}>
                    Helpful {review.helpful_count || 0}
                  </Text>
                </TouchableOpacity>
              </View>
            </View>
          ))
        )}
      </View>

      {/* Add Review Modal */}
      <Modal
        visible={showAddReview}
        animationType="slide"
        transparent={true}
        onRequestClose={() => setShowAddReview(false)}
      >
        <View style={styles.modalOverlay}>
          <View style={styles.modalContainer}>
            <View style={styles.modalHeader}>
              <Text style={styles.modalTitle}>Write a Review</Text>
              <TouchableOpacity onPress={() => setShowAddReview(false)}>
                <Ionicons name="close" size={24} color="#111827" />
              </TouchableOpacity>
            </View>

            <ScrollView style={styles.modalContent}>
              <Text style={styles.modalLabel}>Your Rating</Text>
              <View style={styles.ratingSelector}>
                {[1, 2, 3, 4, 5].map((star) => (
                  <TouchableOpacity
                    key={star}
                    onPress={() => setRating(star)}
                  >
                    <Ionicons
                      name={star <= rating ? 'star' : 'star-outline'}
                      size={36}
                      color="#F59E0B"
                    />
                  </TouchableOpacity>
                ))}
              </View>

              <Text style={styles.modalLabel}>Your Review</Text>
              <TextInput
                style={styles.commentInput}
                placeholder="Share your experience with this product..."
                placeholderTextColor="#9CA3AF"
                multiline
                numberOfLines={5}
                value={comment}
                onChangeText={setComment}
              />
            </ScrollView>

            <View style={styles.modalFooter}>
              <TouchableOpacity
                style={styles.cancelButton}
                onPress={() => setShowAddReview(false)}
              >
                <Text style={styles.cancelButtonText}>Cancel</Text>
              </TouchableOpacity>
              <TouchableOpacity
                style={[
                  styles.submitButton,
                  rating === 0 && styles.submitButtonDisabled,
                ]}
                onPress={handleSubmitReview}
                disabled={rating === 0}
              >
                <Text
                  style={[
                    styles.submitButtonText,
                    rating === 0 && styles.submitButtonTextDisabled,
                  ]}
                >
                  Submit Review
                </Text>
              </TouchableOpacity>
            </View>
          </View>
        </View>
      </Modal>

      <View style={{ height: 40 }} />
    </ScrollView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  summaryContainer: {
    flexDirection: 'row',
    backgroundColor: '#FFFFFF',
    padding: 16,
    marginBottom: 8,
  },
  summaryLeft: {
    alignItems: 'center',
    paddingRight: 24,
    borderRightWidth: 1,
    borderRightColor: '#E5E7EB',
  },
  averageRating: {
    fontSize: 48,
    fontWeight: 'bold',
    color: '#111827',
  },
  stars: {
    flexDirection: 'row',
    gap: 2,
    marginTop: 8,
  },
  totalReviews: {
    fontSize: 13,
    color: '#6B7280',
    marginTop: 8,
  },
  summaryRight: {
    flex: 1,
    paddingLeft: 24,
  },
  ratingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 6,
  },
  ratingStar: {
    fontSize: 12,
    color: '#111827',
    width: 16,
  },
  ratingBar: {
    flex: 1,
    height: 6,
    backgroundColor: '#E5E7EB',
    borderRadius: 3,
    marginHorizontal: 8,
    overflow: 'hidden',
  },
  ratingFill: {
    height: '100%',
    backgroundColor: '#F59E0B',
    borderRadius: 3,
  },
  ratingCount: {
    fontSize: 11,
    color: '#9CA3AF',
    width: 32,
    textAlign: 'right',
  },
  writeReviewButton: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: '#EEF2FF',
    marginHorizontal: 16,
    marginBottom: 8,
    paddingVertical: 12,
    borderRadius: 10,
    gap: 8,
  },
  writeReviewText: {
    fontSize: 14,
    fontWeight: '600',
    color: '#4F46E5',
  },
  reviewsContainer: {
    backgroundColor: '#FFFFFF',
    padding: 16,
  },
  reviewsTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 16,
  },
  noReviews: {
    alignItems: 'center',
    padding: 32,
  },
  noReviewsText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
    marginTop: 16,
  },
  noReviewsSubtitle: {
    fontSize: 14,
    color: '#6B7280',
    marginTop: 8,
    textAlign: 'center',
  },
  reviewCard: {
    borderBottomWidth: 1,
    borderBottomColor: '#F3F4F6',
    paddingBottom: 16,
    marginBottom: 16,
  },
  reviewHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'flex-start',
    marginBottom: 12,
  },
  reviewUser: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  userAvatar: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: '#EEF2FF',
    justifyContent: 'center',
    alignItems: 'center',
  },
  userAvatarText: {
    fontSize: 16,
    fontWeight: 'bold',
    color: '#4F46E5',
  },
  userName: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
  },
  reviewDate: {
    fontSize: 12,
    color: '#9CA3AF',
  },
  reviewComment: {
    fontSize: 14,
    color: '#374151',
    lineHeight: 20,
    marginBottom: 12,
  },
  reviewImages: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 12,
  },
  reviewImage: {
    width: 80,
    height: 80,
    borderRadius: 8,
    backgroundColor: '#F3F4F6',
  },
  reviewFooter: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  helpfulButton: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    paddingHorizontal: 12,
    paddingVertical: 6,
    borderRadius: 16,
    backgroundColor: '#F9FAFB',
  },
  helpfulText: {
    fontSize: 12,
    color: '#6B7280',
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(0, 0, 0, 0.5)',
    justifyContent: 'flex-end',
  },
  modalContainer: {
    backgroundColor: '#FFFFFF',
    borderTopLeftRadius: 20,
    borderTopRightRadius: 20,
    maxHeight: '80%',
  },
  modalHeader: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    padding: 20,
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
  },
  modalContent: {
    padding: 20,
  },
  modalLabel: {
    fontSize: 14,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 12,
  },
  ratingSelector: {
    flexDirection: 'row',
    justifyContent: 'center',
    gap: 12,
    marginBottom: 24,
  },
  commentInput: {
    backgroundColor: '#F9FAFB',
    borderWidth: 1,
    borderColor: '#E5E7EB',
    borderRadius: 12,
    padding: 12,
    fontSize: 14,
    color: '#111827',
    minHeight: 120,
    textAlignVertical: 'top',
  },
  modalFooter: {
    flexDirection: 'row',
    padding: 20,
    gap: 12,
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
  },
  cancelButton: {
    flex: 1,
    paddingVertical: 14,
    borderRadius: 12,
    backgroundColor: '#F3F4F6',
    alignItems: 'center',
  },
  cancelButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#6B7280',
  },
  submitButton: {
    flex: 2,
    paddingVertical: 14,
    borderRadius: 12,
    backgroundColor: '#4F46E5',
    alignItems: 'center',
  },
  submitButtonDisabled: {
    backgroundColor: '#D1D5DB',
  },
  submitButtonText: {
    fontSize: 16,
    fontWeight: '600',
    color: '#FFFFFF',
  },
  submitButtonTextDisabled: {
    color: '#9CA3AF',
  },
});
