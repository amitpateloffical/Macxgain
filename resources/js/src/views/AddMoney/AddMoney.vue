<template>

  <div v-if="showAddMoneyPage" class="add-money-container">
    <div class="add-money-content">
      <h2>Add Money to Your Wallet</h2>
      <div class="qr-container">
        <img src="./image.png" alt="Payment QR Code" class="qr-code" />
        <p class="qr-instruction">Scan & Pay Using PhonePe App</p>
        <p class="qr-account">ADMIN</p>
      </div>
      
      <div class="payment-options">
        <button @click="copyUpiId" class="payment-button">
          <i class="fa-solid fa-copy"></i> Copy UPI ID
        </button>
        <button @click="downloadQR" class="payment-button">
          <i class="fa-solid fa-download"></i> Download QR
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// Add these to your existing script
const showAddMoneyPage = ref(true)

const addMoney = () => {
  showAddMoneyPage.value = true
  sidebarOpen.value = false
}

const copyUpiId = () => {
  const upiId = 'admin.admin@ybl' // Replace with actual UPI ID
  navigator.clipboard.writeText(upiId)
  alert('UPI ID copied to clipboard!')
}

const downloadQR = () => {
  // Implement QR download functionality
  alert('QR download functionality will be implemented here')
}
</script>

<style scoped>
/* Add these styles to your existing CSS */

.add-money-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: calc(100vh - 80px);
  padding: 20px;
  background-color: #f5f7fa;
}

.add-money-content {
  max-width: 800px;
  width: 100%;
  background: white;
  border-radius: 10px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  padding: 30px;
  text-align: center;
}

.add-money-content h2 {
  color: #4a89dc;
  margin-bottom: 30px;
}

.qr-container {
  margin: 0 auto 30px;
  padding: 20px;
  border: 1px dashed #ddd;
  border-radius: 8px;
  display: inline-block;
  background: white;
}

.qr-code {
  width: 200px;
  height: 200px;
  margin-bottom: 15px;
}

.qr-instruction {
  font-weight: bold;
  margin-bottom: 5px;
  color: #333;
}

.qr-account {
  color: #666;
  margin-bottom: 0;
}

.payment-options {
  display: flex;
  justify-content: center;
  gap: 15px;
  margin-top: 30px;
}

.payment-button {
  padding: 10px 20px;
  background-color: #4a89dc;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background-color 0.3s;
}

.payment-button:hover {
  background-color: #3a70c2;
}

/* Responsive adjustments */
@media (max-width: 768px) {
  .add-money-content {
    padding: 20px;
  }
  
  .qr-code {
    width: 180px;
    height: 180px;
  }
  
  .payment-options {
    flex-direction: column;
  }
}

@media (max-width: 480px) {
  .qr-code {
    width: 150px;
    height: 150px;
  }
}
</style>