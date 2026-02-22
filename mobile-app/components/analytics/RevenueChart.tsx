import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
import { LineChart } from 'react-native-gifted-charts';

interface RevenueChartProps {
  data: Array<{
    date: string;
    revenue: number;
  }>;
  title?: string;
  color?: string;
  height?: number;
}

export default function RevenueChart({
  data = [],
  title = 'Revenue Trend',
  color = '#4F46E5',
  height = 200,
}: RevenueChartProps) {
  const formatCurrency = (value: number) => {
    if (value >= 1000000000) {
      return `Rp ${(value / 1000000000).toFixed(1)}B`;
    }
    if (value >= 1000000) {
      return `Rp ${(value / 1000000).toFixed(0)}M`;
    }
    return `Rp ${value.toLocaleString('id-ID')}`;
  };

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return `${date.getDate()}/${date.getMonth() + 1}`;
  };

  const chartData = data.map((item) => ({
    value: item.revenue,
    label: formatDate(item.date),
  }));

  const maxValue = Math.max(...data.map((d) => d.revenue), 0);
  const minValue = Math.min(...data.map((d) => d.revenue), 0);

  if (data.length === 0) {
    return (
      <View style={[styles.container, { height }]}>
        <Text style={styles.emptyText}>No data available</Text>
      </View>
    );
  }

  return (
    <View style={[styles.container, { height }]}>
      <Text style={styles.title}>{title}</Text>
      
      <LineChart
        data={chartData}
        height={height - 60}
        width={undefined}
        color={color}
        thickness={3}
        textColor="#6B7280"
        labelColor="#6B7280"
        yAxisThickness={1}
        yAxisColor="#E5E7EB"
        xAxisThickness={1}
        xAxisColor="#E5E7EB"
        yAxisTextStyle={{ color: '#6B7280', fontSize: 10 }}
        stepValue={maxValue > 0 ? Math.round(maxValue / 5) : 1000000}
        maxValue={maxValue * 1.1}
        minValue={0}
        noOfSections={5}
        showValuesAsTopLabel
        valuePrefix="Rp "
        isAnimated
        curved
        showXAxisIndices={false}
        showYAxisIndices={false}
        hideDataPoints
        spacing={10}
        formatYLabel={(value) => formatCurrency(value)}
      />
      
      <View style={styles.stats}>
        <View style={styles.statItem}>
          <Text style={styles.statLabel}>Highest</Text>
          <Text style={styles.statValue}>{formatCurrency(maxValue)}</Text>
        </View>
        <View style={styles.statItem}>
          <Text style={styles.statLabel}>Lowest</Text>
          <Text style={styles.statValue}>{formatCurrency(minValue)}</Text>
        </View>
        <View style={styles.statItem}>
          <Text style={styles.statLabel}>Average</Text>
          <Text style={styles.statValue}>
            {formatCurrency(data.reduce((sum, d) => sum + d.revenue, 0) / data.length)}
          </Text>
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    backgroundColor: '#FFFFFF',
    borderRadius: 12,
    padding: 16,
    marginHorizontal: 16,
    marginBottom: 16,
  },
  title: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 16,
  },
  emptyText: {
    fontSize: 14,
    color: '#9CA3AF',
    textAlign: 'center',
    marginTop: 40,
  },
  stats: {
    flexDirection: 'row',
    justifyContent: 'space-around',
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#F3F4F6',
  },
  statItem: {
    alignItems: 'center',
  },
  statLabel: {
    fontSize: 11,
    color: '#6B7280',
    marginBottom: 4,
  },
  statValue: {
    fontSize: 13,
    fontWeight: '600',
    color: '#111827',
  },
});
