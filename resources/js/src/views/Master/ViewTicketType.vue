<template>
<div class="dashboard">
     <div class="page__breadcrumb">
     <p> <b-link :to="{ name: 'tickettype' }" >  Ticket Type Master <i class="fas fa-chevron-right"></i></b-link> View</p>
    </div>
    <div class="view-ticket-type">
   <b-card v-if="ticketType" class="details-card">
        <h4>Ticket Type Details</h4>
        <b-card-body>
          <p><strong>Title:</strong> {{ ticketType.title }}</p>
       </b-card-body>
      </b-card>
     <div v-else class="d-flex justify-content-center align-items-center" style="height: 100px;">
    <i class="fas fa-spinner fa-spin fa-3x"></i> <!-- Loader icon -->
  </div>
      <b-button variant="primary" @click="goBack">Go Back</b-button>
    </div>
</div>
  </template>  
  <script>
  import axios from 'axios';
  
  export default {
    data() {
      return {
        ticketType: null,
        loading: true,
        error: null,
      };
    },
    created() {
      this.fetchTicketType();
    },
    setup() {
   

    const goBack = () => {
      window.history.back();
    };
return { goBack };
  },
    methods: {
      fetchTicketType() {
        const id = this.$route.params.id; 
        axios.get(`/api/ticket-type/${id}`)
          .then(response => {
            this.ticketType = response.data.data;
            this.loading = false;
          })
          .catch(error => {
            this.error = "Failed to load ticket type.";
            this.loading = false;
          });
      },
    },
  };
  </script>
  
  <style scoped>
  .view-ticket-type {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  
  }
  .dashboard {
  padding: 20px;
  background-color: #f4f4f4;
  min-height: 100vh;
  width: 100%;
}
  
  .details-card {
    width: 100%; 
    max-width: 500px; 
    margin-top: 20px; 
    text-align: left;
  }
  </style>