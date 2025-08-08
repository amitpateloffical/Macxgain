<template>
    <div class="listing_screen">
      <div class="title_line_screen px-4 py-2">
        <div class="title_line_left">
          <h5>Product</h5>
          <v-select v-if="productData.tabledata.length" :options="filterFieldsData.filterSelectField"
            label="profile_name" class="v_select_profile">
            <template #option="{ profile_name, profile_image }">
              <span class="table_img_cell">
                <span class="table_img">
                  <img src="`../../assest/img/tableprofileimg.png`" alt="profile">
                </span> {{ profile_name }}
              </span>
            </template>
          </v-select>

          <span class="titlelineicon" @click="sidebarstatus.filter = !sidebarstatus.filter"
            v-if="productData.tabledata.length">
            <filterIcon /> Filters
          </span>
          <span class="titlelineicon" v-if="productData.tabledata.length">
            <deleteIcon /> Delete
          </span>
        </div>
        <div class="title_line_right">
          <i class="fa-regular fa-rectangle-list" @click="productData.tableGridStatus = !productData.tableGridStatus"
            v-if="productData.tabledata.length && productData.tableGridStatus"></i>
          <i class="fa-solid fa-table-cells" @click="productData.tableGridStatus = !productData.tableGridStatus"
            v-if="productData.tabledata.length && !productData.tableGridStatus"></i>
          <b-button class="btn_primary me-2" @click="uploadModal.modalStatus = true">Upload</b-button>
          <!-- <b-button class="btn_primary" @click="sidebarstatus.add = !sidebarstatus.add">Add Product</b-button> -->
          <b-button class="btn_primary" :to="{ name: 'admin-add-product' }">Add Product</b-button>
        </div>
      </div>
      <div class="mt-4 px-4">
        <b-overlay :show="productData.loader" rounded="lg" opacity="0.8" class="loader_section" no-wrap>
          <template #overlay>
            <span class="loadersdots"></span>
          </template>
          <div class="table_listing" v-if="productData.tabledata.length">
            <b-table :items="productData.tabledata" :fields="productData.tablefield"
              v-if="productData.tableGridStatus">
              <template #head(select)="data">
                <b-form-checkbox>All</b-form-checkbox>
              </template>
              <template #cell(select)="data">
                <b-form-checkbox></b-form-checkbox>
              </template>
              <template #cell(catalog_image)="data">
                <img 
                  v-if="Array.isArray(parseImages(data.item.uploadedImages)) && parseImages(data.item.uploadedImages).length > 0" 
                  :src="productData.imageurl + data.item.product_id + '/productimages/original/' + parseImages(data.item.uploadedImages)[0]" 
                  alt="Product Image" 
                  width="100"
                  height="100"
                  @click="openModal(data.item)"
                  style="cursor: pointer;"
                />
                <img v-else alt="No Image Available" width="100" height="100" />
              </template>


              <template #cell(name)="data">
                <div class="name_badge" :class="setNameClrLine(data)">
                  <div>{{ data.value }}</div> <span class="on_board" title="On Board"></span>
                </div>
              </template>
              <template #cell(created_at)="data">
                {{ formatDateTime(data.value) }}
              </template>

              <template #cell(actions)="data">
                <div class="table_grid_action">
            <!-- <i class="fa-solid fa-ellipsis" @click="toggletabledropdown($event)"></i> -->
            <div class="table_action_dropdown">
              <ul>
                <li @click="editItem(item)">Edit</li>
                <li @click="makeCopy(item)">Make Copy</li>
                <li @click="deleteItem(item)">Delete</li>
              </ul>
            </div>
          </div>
              </template>
            </b-table>

            
            <div class="table_grid_view" v-else>
          <div class="table_grid_list">
            <div 
              v-for="(item, index) in productData.tabledata" 
              :key="index" 
              class="table_grid_list_item"
              :class="{'border_warning': item.priority === 'warning', 'border_hot': item.priority === 'hot'}"
            >
              <div class="table_grid_title">
                <span class="table_grid_label">#{{ item.product_id || 'N/A' }}</span>
                <span class="table_grid_title_right">
                  <p><span>Request Date:</span> {{ formatDateTime(item.created_at) }}</p>
                  <div class="table_grid_action">
                    <i class="fa-solid fa-ellipsis" @click="toggletabledropdown($event)"></i>
                    <div class="table_action_dropdown">
                      <ul>
                        <li @click="editItem(item)">Edit</li>
                        <li @click="makeCopy(item)">Make Copy</li>
                        <li @click="mergeTicket(item)">Merge Ticket</li>
                        <li @click="closeTicket(item)">Close</li>
                        <li @click="deleteItem(item)">Delete</li>
                      </ul>
                    </div>
                  </div>
                </span>
              </div>
              <div class="table_grid_item_details">
                <h4>{{ item.name || 'Unknown' }}</h4>
                <p>{{ item.description || 'No description available' }}</p>
                <span class="table_grid_sub_label">QTY</span>
                <p>{{ item.netQuantity || 'N/A' }}</p>
              </div>
            </div>
          </div>
        </div>

          </div>
          <div class="No_data_available" v-else>
            <div>
              <img src="../../assest/img/customer/no_customers.svg" alt="no_ticket">
              <p>No Product Found</p>
              <span>Currently, there are no Product in </span>
              <span>the system. </span>
            </div>
          </div>
          
        </b-overlay>
      </div>

        <!-- Modal -->
  <b-modal v-model="showModal" size="lg" hide-footer title="Product Details">
    <template #modal-header>
      <h5 class="modal-title">Product Images</h5>
      <b-button variant="danger" @click="showModal = false">Close</b-button>
    </template>

    <div class="d-flex">
      <!-- Sidebar Thumbnails -->
      <div class="d-flex flex-column align-items-center mr-3">
        <img 
          v-for="(img, index) in parsedImages" 
          :key="index"
          :src="productData.imageurl + selectedProduct?.product_id + '/productimages/original/' + img" 
          width="60" 
          height="60" 
          class="mb-2 border border-primary rounded"
          style="cursor: pointer;"
          @click="selectedImage = img"
        />
      </div>

      <!-- Main Image Display -->
      <div class="flex-grow-1 text-center">
        <img 
          v-if="selectedImage" 
          :src="productData.imageurl + selectedProduct?.product_id + '/productimages/original/' + selectedImage" 
          width="400" 
          height="400" 
          class="border rounded"
        />
      </div>
    </div>
  </b-modal>
  
   

  
      <!--Upload customers Modal-->
      <b-modal id="upload-modal" v-model="uploadModal.modalStatus" hide-header hide-footer ok-title="Upload"
        @ok="uploadFile" size="md" centered>
        <div class="upload_box">
          <h5>Upload File Here
            <CloseIcon class="close_icon_modal" @click="uploadModal.modalStatus = false" />
          </h5>
          <form>
            <input type="file" class="file_input" ref="fileInput" hidden @change="uploadFile">
            <div class="upload_icon" @click="$refs.fileInput.click()">
              <span class="icon_circle">
                <UploadIcon />
              </span>
              <p><span>Choose file</span> to Upload <br> Xls, CSV</p>
            </div>
          </form>
          <section class="loading_area" v-if="showProgress">
            <div class="loading_upload_row" v-for="(file, index) in files">
              <ExcelIcon />
              <div class="content">
                <div class="details">
                  <span class="name">{{ file.name }}</span>
                  <span class="percent">{{ file.loading }}%</span>
                </div>
                <div class="loading_bar">
                  <div class="loading" :style="{ width: file.loading + '%' }"></div>
                </div>
              </div>
            </div>
          </section>
          <section class="upload_area" v-else>
            <div class="loading_upload_row" v-for="(file, index) in uploadFiles">
              <div class="content upload">
                <ExcelIcon />
                <div class="details">
                  <span class="name">{{ file.name }}</span>
                  <span class="size">{{ file.size }}</span>
                </div>
                <div class="loading_bar">
                  <div class="loading" style=""></div>
                </div>
              </div>
            </div>
          </section>
        </div>
      </b-modal>
    </div>
  </template>
  
  <script setup>
  import "vue-select/dist/vue-select.css";
  import axios from "@axios";
  import vSelect from "vue-select";
  import { ref, watch } from "vue";
  import filterIcon from "../../assest/img/icons/Filter.vue"
  import deleteIcon from "../../assest/img/icons/Delete.vue"
  import MinimizeIcon from "../../assest/img/icons/Minimize.vue"
  import MaximizeIcon from "../../assest/img/icons/Maximize.vue"
  import CloseIcon from "../../assest/img/icons/Close.vue"
  import UploadIcon from "../../assest/img/icons/Upload.vue"
  import ExcelIcon from "../../assest/img/icons/Excel.vue"
  import GreenHeartIcon from "../../assest/img/icons/GreenHeart.vue"
  
  
  
  //upload files


  const showModal = ref(false);
  const selectedProduct = ref(null);
  const selectedImage = ref(null);
  const parsedImages = ref([]);

  
  const files = ref([]);
  const uploadFiles = ref([]);
  const showProgress = ref(false);

  const parseImages = (images) => {
    try {
      return JSON.parse(images || '[]');
    } catch (e) {
      return [];
    }
  };

  // Open modal and set images
  const openModal = (product) => {
    selectedProduct.value = product;
    parsedImages.value = parseImages(product.uploadedImages);
    selectedImage.value = parsedImages.value.length > 0 ? parsedImages.value[0] : null;
    showModal.value = true;
  };
  
  const uploadFile = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    const fileName = (file.name.length >= 12) ? file.name.substring(0, 13) + '... .' + file.name.split('.')[1] : file.name;
    const formData = new FormData();
    formData.append("file", file);
    files.value.push({ name: fileName, loading: 0 });
    showProgress.value = true;
  
    axios.post('/abc', formData, {
      onUploadProgress: ({ loaded, total }) => {
        files.value[files.value.length - 1].loading = Math.floor((loaded / total) * 100);
        if (loaded == total) {
          const fileSize = (total < 1024) ? total + 'KB' : (loaded / (1024 * 1024)).toFixed(2) + 'MB';
          uploadFiles.value.push({ name: fileName, size: fileSize });
          files.value = [];
          showProgress.value = false;
        }
      }
    }).catch(console.error(error));
  }
  
  // Comman variables
  
  const uploadModal = ref({
    modalStatus: false
  })
  
  // ------------------------------------------- //
  
  // Table data
  
  const productData = ref({

    tablefield: [
    { key: "select", label: "" },
    // { key: "sno", label: "S.no" },
    { key: "catalog_image", label: "Catalog Image" },
    { key: "category_name", label: "Category" },
    { key: "sub_category_name", label: "Sub Category" },
    { key: "name", label: "Name", sortable: true },
    { key: "product_id", label: "Product Id" },
    { key: "created_at", label: "Created Date" },
    { key: "netQuantity", label: "Products QTY" },
    // { key: "qc_status", label: "QC Status" },
    { key: "actions", label: "Actions" },
  ],

    tabledata: [],
    tableGridStatus: true,
    loader: true,
    currentPage: 1,
    perPage: 10,
    sortBy: '',
    sortDesc: false,
    filterData: {
      ticket_id: '',
      ticket_name: '',
      ticket_type: '',
      priority: '',
    },
    imageurl:null
  });
  
  const filterFieldsData = ref({
    filterSelectField: [
      {
        profile_name: 'Prashant',
        profile_image: 'tableprofileimg.png'
      },
      {
        profile_name: 'Ritesh',
        profile_image: 'tableprofileimg.png'
      },
      {
        profile_name: 'Vishal',
        profile_image: 'tableprofileimg.png'
      },
    ]
  });
  
  const sidebarstatus = ref({
    shadow: false,
    filter: false,
    add: false,
    view: false
  });
  
  const addFormData = ref({
    FormMaxMinStatus: true,
  });
  
  // -------------------------------------------- //
  
  const formatDateTime= (dateString) => {
  const date = new Date(dateString); 
  const day = date.getDate().toString().padStart(2, "0"); 
  const month = date.toLocaleString("default", { month: "short" }); 
  const year = date.getFullYear(); 
  let hours = date.getHours(); 
  const minutes = date.getMinutes().toString().padStart(2, "0"); 
  const ampm = hours >= 12 ? "PM" : "AM";
  hours = hours % 12 || 12; 
  return `${day}/${month}/${year} ${hours}:${minutes} ${ampm}`;
}

  const fetchcustomersData = () => {
    axios
      .get(`/products`, {
        params: {
          page: productData.value.currentPage,
          perPage: productData.value.perPage,
          sortBy: productData.value.sortBy,
          sortDesc: productData.value.sortDesc,
          ...productData.value.filterData
        }
      })
      .then((response) => {
        // console.log(response.data.data.data)
        productData.value.tabledata = response.data.product;
        productData.value.imageurl = response.data.url;
  
        productData.value.loader = false;
      })
      .catch((error) => {
        console.log(error);
      });
  }
  
  fetchcustomersData();
  
  const toggletabledropdown = (evt) => {
    evt.target.nextSibling.classList.toggle("active_table_dropdown");
  }
  
  const setNameClrLine = (data) => {
    if (data.item.engagement == 'High') {
      return "name_badge_red";
    }
    if (data.item.engagement == 'Medium') {
      return "name_badge_orange";
    }
  }
  
  watch(sidebarstatus.value, async (newstatus, oldstatus) => {
    if (newstatus.filter || newstatus.add || newstatus.view) {
      sidebarstatus.value.shadow = true;
    }
    else {
      sidebarstatus.value.shadow = false;
    }
  });
  
  </script>