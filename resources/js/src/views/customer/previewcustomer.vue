<template>
  <div class="view-ticket-type dashboard">
    <b-card v-if="ticketType" class="details-card custom-card">
    <div class="page__breadcrumb">
     <p> <b-link :to="{ name: 'customer' }" >  Customer <i class="fas fa-chevron-right"></i></b-link> Customer Preview</p>
    </div>
      <div class="my-2 d-flex justify-content-end mt-5">
        <b-button variant="info" size="sm" class="mr-1">
          <b-link :to="{ name: '' }">
          </b-link> View Details
        </b-button>
      </div>
      <h4>Customer Preview</h4>
      <b-card-body>
        <p><strong class="active-tickets-title">#CI-00{{ ticketType.id }}</strong></p>
        <strong>Customer Name:</strong>
        <p>{{ ticketType.name }}</p>
        <span class="contact-person">{{ ticketType.contactperson }}</span>
        <span class="contact-info">
          <i class="fas fa-envelope"></i>&nbsp;{{ ticketType.email }}
        </span>
        <span class="contact-info">
          <i class="fas fa-phone"></i>&nbsp;{{ ticketType.contactnumber }}
        </span>
        <div class="customer-preview__details">
          <table class="table">
            <thead>
              <tr>
                <th>Tickets</th>
                <th>Overdue Tickets</th>
                <th>Avg. Response Time</th>
                <th>Total Response Time</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>16</td>
                <td>4</td>
                <td>25:00</td>
                <td>1:32:56</td>
              </tr>
            </tbody>
          </table>
        </div>
        <p><strong class="active-tickets-title">Details</strong></p>
        <div class="details-container">
          <div class="detail-item">
            Org. Name - <strong>qtech software</strong>
          </div>
          <div class="detail-item">
            Assigned To - <strong>admin</strong>
          </div>
          <div class="detail-item">
            Add Line 1 - <strong>Springfield, IL 62701</strong>
          </div>
          <div class="detail-item">
            Add Line 2 - <strong>162 Maple street</strong>
          </div>
          <div class="detail-item">
            State - <strong>maharatra</strong>
          </div>
          <div class="detail-item">
            City - <strong>mumbai</strong>
          </div>
          <div class="detail-item">
            Country - <strong>India</strong>
          </div>
          <div class="detail-item">
            ZIP CODE - <strong>123456</strong>
          </div>
        </div>
        <hr>
        <div class="customer-preview__details">
          <strong class="active-tickets-title">ACTIVE TICKETS</strong>
          <div class="my-2 d-flex justify-content-end mt-0">

            <b-link variant="primary" class="mr-1" @click="showAllTickets = !showAllTickets"
              v-if="getactiveticket.length > 1">
              {{ showAllTickets ? 'Show Less Tickets' : 'View More Tickets' }}
            </b-link>
          </div>
          <div v-if="getactiveticket.length > 0" class="mt-3">
            <p><strong>#TI-{{ getactiveticket[0].id }} | {{ getactiveticket[0].name }}</strong></p>
            <table class="table">
              <thead>
                <tr>
                  <th>Tickets Type</th>
                  <th>Priority</th>
                  <th>Assigned To</th>
                  <th>Request Date</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>{{ getactiveticket[0].ticket_type }}</td>
                  <td>{{ getactiveticket[0].priority }}</td>
                  <td>{{ getactiveticket[0].assignee_name }}</td>
                  <td>{{ getactiveticket[0].request_date }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <div v-if="showAllTickets">
            <div v-for="(ticket, index) in getactiveticket" :key="`ticket-${index}`" class="mt-3">
              <p><strong>#TI-{{ ticket.id }}</strong></p>
              <h5 class="table-title">Ticket Details</h5>
              <table class="table">
                <thead>
                  <tr>
                    <th>Tickets Type</th>
                    <th>Priority</th>
                    <th>Assigned To</th>
                    <th>Request Date</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>{{ ticket.ticket_type }}</td>
                    <td>{{ ticket.priority }}</td>
                    <td>{{ ticket.assignee_name }}</td>
                    <td>{{ ticket.request_date }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <hr>
        <h5 class="active-tickets-title">TASK</h5>
        <div v-for="(task, index) in gettaskdata" :key="`task-${index}`" class="mt-3">
          <div>
            <strong>* Task Id-{{ task.id }} {{ task.title }}</strong>
            <p>{{ task.description }}</p>
            {{ formatDateTime(task.date, task.time) }}
          </div>
          <hr v-if="index < gettaskdata.length - 1" class="divider">
        </div>
        <hr>
        <h5 class="active-tickets-title">ACTIVITIES LOGS</h5>
        <div v-for="(activity, index) in getactivitydata" :key="`activity-${index}`" class="mt-3">
          <div>
            <strong>* Task Id-{{ activity.id }} {{ activity.title }}</strong>
            <p>{{ activity.description }}</p>
            {{ formatDateTime(activity.date, activity.time) }}
          </div>
          <hr v-if="index < getactivitydata.length - 1" class="divider">
        </div>
      </b-card-body>
    </b-card>
    <div v-else>
      <p>Loading...</p>
    </div>
    <b-button variant="primary" @click="goBack">Go Back</b-button>
  </div>
</template>
<script>
import axios from '@axios';
export default {
  data() {
    return {
      ticketType: null,
      loading: true,
      error: null,
      gettaskdata: [],
      getactivitydata: [],
      getactiveticket: [],
      showAllTickets: false,
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
  mounted() {
    this.fetchtask();
    this.fetchactiveticket();
  },
  methods: {
    formatDateTime(dateString, timeString) {
      const combinedDateTime = `${dateString}T${timeString}`;
      const date = new Date(combinedDateTime);
      if (isNaN(date.getTime())) {
        return 'Invalid Date';
      }
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
      return date.toLocaleString('en-US', options);
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      const monthNames = [
        "January", "February", "March",
        "April", "May", "June", "July",
        "August", "September", "October",
        "November", "December"
      ];
      const day = date.getDate();
      const monthIndex = date.getMonth();
      return monthNames[monthIndex] + ' ' + day;
    },
    fetchactivity() {
      const id = this.$route.params.id;
      axios.get(`/getactivity/${id}`)
        .then(response => {
          this.getactivitydata = response.data.data;
          console.log(response.data.data, 'sd')
          this.loading = false;
        })
        .catch(() => {
          this.error = "Failed to load ticket type.";
          this.loading = false;
        });
    },
    fetchactiveticket() {
      const id = this.$route.params.id;
      axios.get(`/getactivetickets/${id}`)
        .then(response => {
          this.getactiveticket = response.data.data;
          console.log(response.data.data, 'sd')
          this.loading = false;
        })
        .catch(() => {
          this.error = "Failed to load ticket type.";
          this.loading = false;
        });
    },

    fetchtask() {
      const id = this.$route.params.id;
      axios.get(`/getcustomertask/${id}`)
        .then(response => {
          this.gettaskdata = response.data.data;
          console.log(response.data.data, 'sd')
          this.loading = false;
        })
        .catch(() => {
          this.error = "Failed to load ticket type.";
          this.loading = false;
        });
    },
    fetchTicketType() {
      const id = this.$route.params.id;
      console.log(id, 'id')
      axios.get(`/customer/${id}`)
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
  min-height: 100vh;
}

.details-card {
  width: 100%;
  max-width: 500px;
  margin-top: 20px;
  text-align: left;
}
</style>
<style scoped>
.custom-card {
  width: 80%;
  max-width: 800px;
  height: auto;
  margin: auto;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 20px;
}
.contact-person {
  margin-right: 40px;
}
.contact-info {
  margin-left: 20px;
}
.details-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
}
.detail-item {
  width: 48%;
  margin-bottom: 10px;
}
.active-tickets-title {
  color: #00b7ff;
}
.task-item {
  margin-bottom: 20px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
  background-color: #f9f9f9;
}
.active-tickets-title {
  margin-bottom: 15px;
}
.divider {
  margin: 10px 0;
}
.dashboard {
  padding: 20px;
  background-color: #f4f4f4;
  min-height: 100vh;
  width: 100%;
}

</style>