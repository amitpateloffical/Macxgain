<template>
  <canvas id="pieChart"></canvas>
</template>

<script>
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  props: {
    chartData: {
      type: Object,
      required: true
    }
  },
  watch: {
    chartData: {
      handler(newValue) {
        this.renderChart(newValue);
      },
      deep: true // Watch for deep changes in chartData
    }
  },
  mounted() {
    this.renderChart(this.chartData);
  },
  methods: {
    renderChart(data) {
      const ctx = document.getElementById('pieChart').getContext('2d');
      // Destroy the previous chart instance if it exists
      if (this.chart) {
        this.chart.destroy();
      }
      this.chart = new Chart(ctx, {
        type: 'pie',
        data: data,
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'top'
            },
            title: {
              display: false
            }
          }
        }
      });
    }
  },
  beforeDestroy() {
    if (this.chart) {
      this.chart.destroy(); // Clean up the chart instance
    }
  }
};
</script>

<style scoped>
/* Add your styles here */
</style>
