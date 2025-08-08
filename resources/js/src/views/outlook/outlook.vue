<template>
    <div class="dashboard">
      <div class="list__page">
        <div v-if="successMessage" class="alert alert-success">
          {{ successMessage }}
        </div>
          <h4><i class="fas fa-ticket"></i> Customer </h4>
          <!-- Upload button that triggers the modal -->
    <b-button
      variant="primary"
      class="mb-0 ml-md-1 basicButton shift3"
      @click="modalVisible = true"
      v-ripple.400="'rgba(113, 102, 240, 0.15)'"
    >
      Upload
    </b-button>

    <b-button variant="primary"  v-if="!showSidebar" class="mb-0 ml-md-1 basicButton shift3" v-ripple.400="'rgba(113, 102, 240, 0.15)'"
          @click="checkauth()">
          auth
        </b-button>

        <b-button variant="primary"  v-if="!showSidebar" class="mb-0 ml-md-1 basicButton shift3" v-ripple.400="'rgba(113, 102, 240, 0.15)'"
          @click="getInbox()">
          getInbox
        </b-button>
    
        
        
    
      </div>
    </div>
  </template>
  <script>
  import "vue-select/dist/vue-select.css";
  import axios from "@axios";
  import vSelect from "vue-select";
  import { ref } from "vue";
  export default {
  components: { vSelect },
  computed: {
    sortIcon() {
      return this.sortDesc ? "fas fa-sort-down" : "fas fa-sort-up";
    },
    rangeStart() {
      if (this.totalrows === 0) {
        return 0;
      } else {
        return (this.currentPage - 1) * this.perPage + 1;
      }
    },
    rangeEnd() {
      if (this.currentPage * this.perPage >= this.totalrows) {
        return this.totalrows;
      } else {
        return this.currentPage * this.perPage;
      }
    },
  },
  data() {
    const encodeBase64 = (data) => {
      return btoa(data.toString());
    };
    return {
        showSidebar: false, // Sidebar visibility control
        TickeListData: [],
        fields: [
          { key: "customer_id", label: "Customer ID" },
          { key: "name", label: "Name", sortable: true },
          { key: "contactperson", label: "Contact Person" },
          { key: "email", label: "Email" },
          { key: "contactnumber", label: "Contact No." },
          { key: "assigned_to", label: "Assigned To" },
          { key: "engagement", label: "Engagement" },
          { key: "actions", label: "Action" },
  
        ],
        TicketData: {
          name: "",
          contactperson: "",
          email: null,
          contactnumber: null,
          assignee: null,
          tags: [],
          followers: [],
          message_from: null,
          message: "",
        },
        totalrows: 0,
        encodeBase64,
        sortBy: "",
        sortDesc: false,
        successMessage: "",
        showfilter: false,
        modalVisible: false,
        file: null,
        successMessage: "",
        deviceCodeData: null,
        accessToken: null,
        tokenPollInterval: null,
      };
  },
  mounted() {
    this.fetchEntries();
    axios.get(`/get-ticket-type`).then((response) => {
      this.ticketTypeOptions = response.data.data;
      this.priorityOptions = response.data.priorityOptions
    });
    axios.get(`/get-ticket-teg`).then((response) => {
      this.tagOptions = response.data.data;
    });
    axios.get("/alluser").then((response) => {
      this.assigneeOptions = response.data.alluser;
      this.requesterOptions = response.data.alluser;
      this.followerOptions = response.data.alluser;
    });
  },
    watch: {
    currentPage(newPage) {
      this.fetchEntries();
    },
    perPage(newPerPage) {
      this.currentPage = 1;
      this.fetchEntries();
    },
  },
    methods: {
        showModal() {
            this.modalVisible = true;
        },
        handleFileUpload(event) {
          const file = event.target.files[0];
          this.file = file;  // Store the file for later use
        },


        async checkauth() {
          try {
            const response = await axios.get('/outlook/authenticate');
            this.deviceCodeData = response.data;
            alert(`To authenticate, go to ${this.deviceCodeData.verification_url} and enter the code: ${this.deviceCodeData.device_code}`);
            this.tokenPollInterval = setInterval(this.fetchAccessToken, 5000); 
          } catch (error) {
            console.error('Error initiating authentication:', error);
          }
        },

        async fetchAccessToken() {
          try {
            const response = await axios.get('/outlook/get-access-token');
            if (response.data['User token']) {
              this.accessToken = response.data['User token'];
              alert(`Access Token: ${this.accessToken}`);

              // Clear polling once we have the token
              clearInterval(this.tokenPollInterval);
              this.tokenPollInterval = null;
            }
          } catch (error) {
            console.error('Still waiting for user to authenticate...');
          }
        },


        checkauthc() {
          axios
            .get("/outlook/authenticate", )
            .then((response) => {

                console.log(response);
            })
            .catch((error) => {
              console.error("Upload failed:", error);
            });
        },


        getInbox() {
          axios
            .get("/outlook/getInbox", )
            .then((response) => {

                console.log(response);
            })
            .catch((error) => {
              console.error("Upload failed:", error);
            });
        },



        downloadSampleFile() {
          window.location.href = '/files/customers_dummy_data.xlsx';
        },
      resetForm() {
        this.TicketData = {
          id: null,
          name: "",
          contactperson: "",
          email: null,
          contactnumber: null,
          assignee: null,
          tags: [],
          followers: [],
          message_from: null,
          message: "",
        };
      },
      onSortChanged(ctx) {
        this.sortBy = ctx.sortBy;
        this.sortDesc = ctx.sortDesc;
      },
      changeSorting(field) {
        if (this.sortBy === field) {
          this.sortDesc = !this.sortDesc;
        } else {
          this.sortBy = field;
          this.sortDesc = false;
        }
        this.fetchEntries();
      },
      getSortIcon(field) {
        if (this.sortBy === field) {
          return this.sortDesc ? "fas fa-sort-down" : "fas fa-sort-up";
        }
        return "fas fa-sort";
      },
  
      resetFilter() {
        (this.filterData = {
          customer_id: "",
          name: "",
          email: "",
          contactperson: "",
        }),
          (this.currentPage = 1);
        this.fetchEntries();
        this.showfilter = false;
      },
      searchFilter() {
        this.currentPage = 1;
        this.fetchEntries();
        this.showfilter = false;
      },
      fetchEntries() {
        axios
          .get("/auth/token", {
            params: {
              page: this.currentPage,
              perPage: this.perPage,
              ...this.filterData,
            },
          })
          .then((response) => {
            this.TickeListData = response.data.data.data;
            this.totalrows = response.data.data.total;
          })
          .catch((error) => {});
      },
      clearSuccessMessage() {
        setTimeout(() => {
          this.successMessage = "";
        }, 2000);
      },
    },
    setup() {
      const filterData = ref({
        customer_id: "",
        name: "",
        email: "",
        contactperson: "",
        engagement:''
      });
      const currentPage = ref(1);
      const perPage = ref(10);
      const perPageOptions = [10, 50, 75, 100];
      const RemoveError = (errorName) => {
        errors.value[errorName] = " ";
      };
      const hasErrors = (fieldName) => {
        return fieldName in errors.value;
      };
      const getErrors = (fieldName) => {
        return errors.value[fieldName][0];
      };
  
      const errors = ref([]);
      
      return {
        RemoveError,
        filterData,
        hasErrors,
        getErrors,
        errors,
        currentPage,
        perPage,
        perPageOptions,
      };
    },
  setup() {
    const filterData = ref({
      ticket_id: '',
      ticket_name: '',
      ticket_type: '',
      priority: '',
    });
    const currentPage = ref(1);
    const perPage = ref(10);
    const perPageOptions = [10, 50, 75, 100];
  
    const RemoveError = (errorName) => {
      errors.value[errorName] = " ";
    };
    const hasErrors = (fieldName) => {
      return fieldName in errors.value;
    };
  
    const getErrors = (fieldName) => {
      return errors.value[fieldName][0];
    };
  
    const errors = ref([]);
  
    return {
      RemoveError,
      filterData,
      hasErrors,
      getErrors,
      errors,
      currentPage,
      perPage,
      perPageOptions,
    };
  },
  };
  </script>
  
  <style scoped>
.dashboard {
  padding: 20px;
  background-color: #f4f4f4;
  min-height: 100vh;
  width: 100%;
}
  
  .sidebar-like {
  background-color: white;
  border: 1px solid #ddd;
  padding: 20px;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
  }
  
  .d-flex {
  display: flex;
  align-items: center;
  }
  
  .ml-2 {
  margin-left: 0.5rem;
  }
  </style>