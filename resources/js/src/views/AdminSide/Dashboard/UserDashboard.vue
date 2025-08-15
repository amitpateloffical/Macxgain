<template>
    <div class="dashboard_screen">
      <div class="dashboard_header">
        <h5 class="dashboard_title">User Dashboard</h5>
        <div class="dashboard_selector">
          <v-select 
            :options="['Ticket', 'Customer Health']" 
            v-model="dashboardshow" 
            placeholder="Select" 
            closeOnSelect="false" 
            clearSearchOnSelect="false" 
            clearable="false"
          />      
        </div>
      </div>
      <dashboardTicket v-if="dashboardshow == 'Ticket'" />
      <dashboardHealth v-else />
      
    </div>
  </template>
  
  <script>
  import "vue-select/dist/vue-select.css";
  import vSelect from "vue-select";
  import dashboardTicket from '../../DashboardTicket.vue';
  import dashboardHealth from '../../DashboardHealth.vue';
  
  
  export default {
    components: {
      vSelect,
      dashboardTicket,
      dashboardHealth
    },
    data() {
      return{
        ltfields:['ticket_ID', 'subject', 'priority', 'Type', 'client', 'request_date', 'action'],
        ltitems: [
            { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'High', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
            { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'High', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
            { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'Medium', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
            { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'Low', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
            { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'High', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
            { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'Medium', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
            { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'High', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
            { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'Low', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
            { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'High', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
          ],
          dashboardshow: 'Ticket'
      }
    },
    mounted() {
      
    },
    methods: {
  
    }
  };
  </script>

<style scoped>
.dashboard_screen {
  padding: 1.5rem;
  background-color: #f8f9fa;
  min-height: 100vh;
}

.dashboard_header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  margin-bottom: 2rem;
  gap: 1rem;
}

.dashboard_title {
  font-size: clamp(1.25rem, 3vw, 1.75rem);
  font-weight: 600;
  color: #333;
  margin: 0;
}

.dashboard_selector {
  min-width: 200px;
}

/* Responsive breakpoints */
@media (max-width: 768px) {
  .dashboard_screen {
    padding: 1rem;
  }
  
  .dashboard_header {
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
  }
  
  .dashboard_selector {
    width: 100%;
    min-width: auto;
  }
}

@media (max-width: 480px) {
  .dashboard_screen {
    padding: 0.75rem;
  }
  
  .dashboard_header {
    margin-bottom: 1.5rem;
  }
  
  .dashboard_title {
    font-size: 1.25rem;
  }
}

/* Landscape orientation for mobile */
@media (max-width: 768px) and (orientation: landscape) {
  .dashboard_header {
    flex-direction: row;
    align-items: center;
  }
  
  .dashboard_selector {
    width: auto;
    min-width: 180px;
  }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
  .dashboard_screen {
    background-color: #1a1a2e;
  }
  
  .dashboard_title {
    color: #ffffff;
  }
}
</style>
  