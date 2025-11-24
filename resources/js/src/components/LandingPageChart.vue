<template>
  <div class="landing-chart-container">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export default {
  name: 'LandingPageChart',
  props: {
    chartType: {
      type: String,
      default: 'line'
    },
    data: {
      type: Array,
      default: () => []
    },
    primaryColor: {
      type: String,
      default: '#FFD700'
    }
  },
  data() {
    return {
      chart: null
    };
  },
  mounted() {
    this.createChart();
  },
  beforeUnmount() {
    if (this.chart) {
      this.chart.destroy();
    }
  },
  watch: {
    data: {
      handler() {
        if (this.chart) {
          this.chart.destroy();
        }
        this.createChart();
      },
      deep: true
    },
    primaryColor() {
      if (this.chart) {
        this.chart.destroy();
      }
      this.createChart();
    }
  },
  methods: {
    createChart() {
      if (!this.$refs.chartCanvas) return;

      const ctx = this.$refs.chartCanvas.getContext('2d');
      
      // Generate sample data if not provided
      const chartData = this.data.length > 0 ? this.data : this.generateSampleData();
      
      // Convert hex to RGB
      const rgb = this.hexToRgb(this.primaryColor);
      const rgba = rgb ? `rgba(${rgb.r}, ${rgb.g}, ${rgb.b}, 0.2)` : 'rgba(255, 215, 0, 0.2)';
      const borderColor = this.primaryColor;

      this.chart = new Chart(ctx, {
        type: this.chartType,
        data: {
          labels: chartData.map((_, i) => i + 1),
          datasets: [{
            label: 'Performance',
            data: chartData,
            backgroundColor: rgba,
            borderColor: borderColor,
            borderWidth: 2,
            fill: true,
            tension: 0.4,
            pointRadius: 0,
            pointHoverRadius: 6,
            pointHoverBackgroundColor: borderColor,
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 2
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: false
            },
            tooltip: {
              enabled: true,
              backgroundColor: 'rgba(0, 0, 0, 0.8)',
              titleColor: '#fff',
              bodyColor: '#fff',
              borderColor: borderColor,
              borderWidth: 1,
              padding: 12,
              displayColors: false
            }
          },
          scales: {
            x: {
              display: false,
              grid: {
                display: false
              }
            },
            y: {
              display: false,
              grid: {
                display: false
              }
            }
          },
          interaction: {
            intersect: false,
            mode: 'index'
          }
        }
      });
    },
    generateSampleData() {
      const data = [];
      const baseValue = 100;
      for (let i = 0; i < 30; i++) {
        data.push(baseValue + Math.sin(i / 3) * 20 + Math.random() * 10);
      }
      return data;
    },
    hexToRgb(hex) {
      const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
      return result ? {
        r: parseInt(result[1], 16),
        g: parseInt(result[2], 16),
        b: parseInt(result[3], 16)
      } : null;
    }
  }
};
</script>

<style scoped>
.landing-chart-container {
  width: 100%;
  height: 100%;
  position: relative;
}
</style>

