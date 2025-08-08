<template>
  <div class="dashboard_screen p-4">
    <div class="title_line_screen">
      <h5>Dashboard</h5>
      <v-select :options="['Ticket', 'Customer Health']" v-model="dashboardshow" placeholder="Select" closeOnSelect="false" clearSearchOnSelect="false" clearable="false"></v-select>      
    </div>
    <dashboardTicket v-if="dashboardshow == 'Ticket'" />
    <dashboardHealth v-else />
    
  </div>
</template>

<script>
import "vue-select/dist/vue-select.css";
import vSelect from "vue-select";
import dashboardTicket from '../views/DashboardTicket.vue';
import dashboardHealth from '../views/DashboardHealth.vue';


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
