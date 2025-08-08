<template>
  <div class="dashboard">
    <div class="page__breadcrumb">
      <p>
        <b-link :to="{ name: 'admin-product' }">
          Product <i class="fas fa-chevron-right"></i>
        </b-link>
        Add Product
      </p>
    </div>

    <b-row>
      <!-- Categories List -->
      <b-col md="2">
        <div class="list-container">
          <div class="list-group" style="max-height: 400px; overflow-y: auto">
            <button
              v-for="category in categories"
              :key="category.id"
              @click="selectCategory(category)"
              :class="[
                'list-group-item list-group-item-action',
                { active: category.id === selectedCategoryId },
              ]"
            >
              {{ category.name }}
            </button>
          </div>
        </div>
      </b-col>

      <!-- Subcategories List -->
      <b-col md="2">
        <div class="list-container">
          <div class="list-group" style="max-height: 400px; overflow-y: auto">
            <button
              v-for="subcategory in categories.find(
                (c) => c.id === selectedCategoryId
              )?.subcategories || []"
              :key="subcategory.id"
              @click="selectSubcategory(subcategory)"
              :class="[
                'list-group-item list-group-item-action',
                { active: subcategory.id === selectedSubcategoryId },
              ]"
            >
              {{ subcategory.name }}
            </button>
          </div>
        </div>
      </b-col>
      <b-col
        md="7"
        style="border: 1px solid #000; max-height: 400px; overflow: auto"
        v-if="product.sub_category != null"
      >
        <div class="file-upload-container">
          <!-- Uploaded Images -->
          <div
            v-for="(image, index) in product.uploadedImages"
            :key="index"
            class="uploaded-image-container"
          >
            <button class="close-icon" @click="removeImage(index)">×</button>
            <img :src="image" alt="Uploaded" class="uploaded-image" />
          </div>

          <!-- File Input Box -->
          <label
            v-if="product.uploadedImages.length < 10"
            class="custom-file-input"
          >
            <div class="file-input-box">
              <div class="icon">+</div>
              <div class="text">Add Front Images</div>
            </div>
            <input
              type="file"
              accept="image/*"
              hidden
              @change="handleFileUpload"
              multiple
            />
          </label>

          <!-- Warning Message -->
          <p v-if="product.uploadedImages.length >= 10" class="warning-message">
            You can upload a maximum of 10 images.
          </p>
        </div>
      </b-col>
    </b-row>

    <b-row
      style="margin-top: 100px; overflow: auto"
      v-if="product.uploadedImages != 0"
    >
      <!-- Product Basic Details -->
      <b-col md="3">
        <label class="form-label required">Net Weight (gms) </label>
        <b-form-group label-for="net-weight">
          <b-form-input
            id="net-weight"
            v-model="product.netWeight"
            @keypress="numberValidate"
            placeholder="Enter Net Weight (gms)"
            @input="removeError('netWeight')"

          ></b-form-input>
          <small class="text-danger">{{ errors[0] }}</small>
            <div class="text-danger" v-if="hasErrors('netWeight')">
              {{ getErrors("netWeight") }}
            </div>
        </b-form-group>
      </b-col>

      <b-col md="3">
        <label class="form-label required"> Product Name </label>
        <b-form-group label-for="product-name" required>
          <b-form-input
            id="product-name"
            v-model="product.name"
            placeholder="Enter Product Name"
            @input="removeError('name')"

          ></b-form-input>
          <small class="text-danger">{{ errors[0] }}</small>
            <div class="text-danger" v-if="hasErrors('name')">
              {{ getErrors("name") }}
            </div>
        </b-form-group>
      </b-col>

      <b-col md="3">
        <label class="form-label required">
          Style Code/Product ID (Optional)
        </label>

        <b-form-group
          label-for="product-id"
          description="Click Generate to create a random ID."
        >
          <b-input-group>
            <b-form-input
              id="product-id"
              v-model="product.product_id"
              placeholder="Enter Style Code/Product ID"
              readonly
            @input="removeError('product_id')"

            ></b-form-input>
            <b-input-group-append>
              <b-button variant="danger" @click="generateId();removeError('product_id')"
                >Generate</b-button
              >
            </b-input-group-append>
          </b-input-group>
          <small class="text-danger">{{ errors[0] }}</small>
          <div class="text-danger" v-if="hasErrors('product_id')">
            {{ getErrors("product_id") }}
          </div>
        </b-form-group>
      </b-col>

      <b-col md="3">
        <label class="form-label required"> Size</label>
        <b-form-group label-for="product-size" required>{{ product.size }}
          <b-form-select
            sm
            id="product-size"
            v-model="product.size"
            :options="sizeOptions"
            multiple
            @input="updateTableRows();removeError('size')"
          ></b-form-select>
          <small class="text-danger">{{ errors[0] }}</small>
            <div class="text-danger" v-if="hasErrors('size')">
              {{ getErrors("size") }}
            </div>
        </b-form-group>
      </b-col>

      <div class="table_listing">
        <b-table :items="product.tableData" :fields="tableFields" bordered>
          <!-- Display size column -->
          <template #cell(Size)="data">
            {{ data.item.size }}
          </template>
          <template #cell(listing_price)="data">
            <b-form-input
              v-model="data.item.listing_price"
              placeholder="Enter Meesho price"
              @keypress="numberValidate"
            ></b-form-input>
          </template>
          <template #cell(WrongDefectiveReturnsPrice)="data">
            <b-form-input
              v-model="data.item.wrongDefectiveReturnsPrice"
              placeholder="Enter wrong/defective returns price"
              @keypress="numberValidate"
            ></b-form-input>
          </template>
          <template #cell(MRP)="data">
            <b-form-input
              v-model="data.item.mrp"
              placeholder="Enter MRP"
              @keypress="numberValidate"
            ></b-form-input>
          </template>

          <!-- Delete action -->
          <template #cell(Actions)="data">
            <b-button variant="danger" @click="deleteRow(data.item)"
              >Delete</b-button
            >
          </template>
        </b-table>
      </div>

      <!-- Product Details -->
      <b-col md="3">
        <label class="form-label required"> Net Quantity (N)</label>

        <b-form-group label-for="net-quantity" required>
          <b-form-input
            id="net-quantity"
            v-model="product.netQuantity"
            @keypress="numberValidate"
            placeholder="Enter Net Quantity"
            @input="removeError('netQuantity')"
          ></b-form-input>
          <small class="text-danger">{{ errors[0] }}</small>
            <div class="text-danger" v-if="hasErrors('netQuantity')">
              {{ getErrors("netQuantity") }}
            </div>
        </b-form-group>
      </b-col>

      <b-col md="3">
        <label class="form-label required"> Warranty Period</label>
        <b-form-group label-for="warranty-period" required>
          <b-form-select
            id="warranty-period"
            v-model="product.warrantyPeriod"
            :options="warrantyPeriodOptions"
            @input="removeError('warrantyPeriod')"

          ></b-form-select>
          <small class="text-danger">{{ errors[0] }}</small>
            <div class="text-danger" v-if="hasErrors('warrantyPeriod')">
              {{ getErrors("warrantyPeriod") }}
            </div>
        </b-form-group>
      </b-col>

      <b-col md="3">
        <label class="form-label required"> Warranty Type</label>
        <b-form-group label-for="warranty-type" required>
          <b-form-select
            id="warranty-type"
            v-model="product.warrantyType"
            :options="warrantyTypeOptions"
            @input="removeError('warrantyType')"

          ></b-form-select>
          <small class="text-danger">{{ errors[0] }}</small>
            <div class="text-danger" v-if="hasErrors('warrantyType')">
              {{ getErrors("warrantyType") }}
            </div>
        </b-form-group>
      </b-col>

      <!-- <b-col md="4">
          <b-form-group
            label="Country of Origin"
            label-for="country-origin"
            required
          >
            <b-form-select
              id="country-origin"
              v-model="product.countryOfOrigin"
              :options="countryOptions"
            ></b-form-select>
          </b-form-group>
        </b-col> -->

      <b-col md="3">
        <label class="form-label required"> Manufacturer Details</label>

        <b-form-group label-for="manufacturer-details" required>
          <b-form-input
            id="manufacturer-details"
            v-model="product.manufacturerDetails"
            placeholder="Enter Manufacturer Details"
            @input="removeError('manufacturerDetails')"

          ></b-form-input>
          <small class="text-danger">{{ errors[0] }}</small>
            <div class="text-danger" v-if="hasErrors('manufacturerDetails')">
              {{ getErrors("manufacturerDetails") }}
            </div>
        </b-form-group>
      </b-col>

      <b-col md="3">
        <label class="form-label required"> Packer Details</label>

        <b-form-group label-for="packer-details" required>
          <b-form-input
            id="packer-details"
            v-model="product.packerDetails"
            placeholder="Enter Packer Details"
            @input="removeError('packerDetails')"

          ></b-form-input>
          <small class="text-danger">{{ errors[0] }}</small>
            <div class="text-danger" v-if="hasErrors('packerDetails')">
              {{ getErrors("packerDetails") }}
            </div>
        </b-form-group>
      </b-col>

      <!-- Other Attributes -->
      <b-col md="3">
        <label class="form-label required"> Brand</label>

        <b-form-group label-for="brand" required>
          <b-form-select
            id="brand"
            v-model="product.brand"
            :options="brandOptions"
            placeholder="Select Brand"
            @input="removeError('brand')"

          ></b-form-select>
          <small class="text-danger">{{ errors[0] }}</small>
            <div class="text-danger" v-if="hasErrors('brand')">
              {{ getErrors("brand") }}
            </div>
        </b-form-group>
      </b-col>
      <b-row>
        <b-col md="6">
          <label class="form-label required"> Description</label>

          <b-form-group label-for="description">
            <b-form-textarea
              id="description"
              v-model="product.description"
              placeholder="Enter Description"
              rows="3"
            @input="removeError('description')"
            ></b-form-textarea>
            <small class="text-danger">{{ errors[0] }}</small>
            <div class="text-danger" v-if="hasErrors('description')">
              {{ getErrors("description") }}
            </div>
          </b-form-group>
        </b-col>

        <b-col md="6">
          <label class="form-label required"> Importer Details</label>

          <b-form-group label-for="importer-details">
            <b-form-textarea
              id="importer-details"
              v-model="product.importerDetails"
              placeholder="Enter Importer Details"
              rows="3"
            ></b-form-textarea>
          </b-form-group>
        </b-col>
      </b-row>

      <div class="sidebarbtn_group">
        <b-button
          class="btn_secondary_border me-2"
          @click="sidebarstatus.filter = !sidebarstatus.filter"
          >Cancel</b-button
        >
        <b-button @click="fromSubmit()" class="btn_primary">Submit</b-button>
      </div>
    </b-row>
  </div>
</template>
  
  <script  >
import axios from "@axios";
import vSelect from "vue-select";
import { ref, watch, computed } from "vue";
import { toast } from "vue3-toastify";
import "vue3-toastify/dist/index.css";
import { number } from "yup";
import { useRouter } from 'vue-router';


export default {
  components: {
    vSelect,
  },
  data() {
    return {
      sizeOptions: [
        { text: "1 Mtr", value: "1 Mtr" },
        { text: "1.75 Mtr", value: "1.75 Mtr" },
        { text: "2 Mtr", value: "2 Mtr" },
        { text: "2.5 Mtr", value: "2.5 Mtr" },
        { text: "3 Mtr", value: "3 Mtr" },
        { text: "4 Mtr", value: "4 Mtr" },
        { text: "5 Mtr", value: "5 Mtr" },
        { text: "6 Mtr", value: "6 Mtr" },
      ],
      tableData: [], // Holds the rows for the table
      tableFields: [
        { key: "Size", label: "Size" },
        { key: "listing_price", label: "Listing Price*" },
        {
          key: "WrongDefectiveReturnsPrice",
          label: "Wrong/Defective Returns Price",
        },
        { key: "MRP", label: "MRP*" },
        { key: "Actions", label: "Actions" },
      ],
      categories: [],
      selectedCategoryId: null,
      selectedSubcategoryId: null,
      product: {
        category: null,
        sub_category: null,
        uploadedImages: [],
        size: [],
        product_id: null,
        tableData: [],
      },
      uploadedImages: [],
      warrantyPeriodOptions: ["1 Month", "6 Months", "1 Year", "2 Years"],
      warrantyTypeOptions: [
        "Not Applicable",
        "Manufacturer Warranty",
        "Seller Warranty",
      ],
      countryOptions: ["India", "USA", "China", "Other"],
      brandOptions: ["Brand A", "Brand B", "Brand C"],
      errors: [],
    };
  },
  computed: {
    selectedCategory() {
      return (
        this.categories.find(
          (category) => category.id === this.product.category
        ) || null
      );
    },
  },
  mounted() {
    this.fetchCategories();
  },
  methods: {
    numberValidate(event) {
      const charCode = event.charCode;
      if (
        charCode !== 8 && // Allow backspace
        charCode !== 0 && // Allow control keys (e.g., tab)
        (charCode < 48 || charCode > 57) // Restrict to numeric keys (0-9)
      ) {
        event.preventDefault(); // Block invalid input
      }
    },
    generateId() {
      const randomId = `P-${Math.floor(10000 + Math.random() * 90000)}`;
      this.product.product_id = randomId;
    },
    updateTableRows() {
      // Add rows for newly selected sizes
      this.product.size.forEach((size) => {
        if (!this.product.tableData.some((row) => row.size === size)) {
          this.product.tableData.push({
            size,
            listing_price: "",
            wrongDefectiveReturnsPrice: "",
            mrp: "",
          });
        }
      });

      // Remove rows for sizes that are deselected
      this.product.tableData = this.product.tableData.filter((row) =>
        this.product.size.includes(row.size)
      );
    },
    deleteRow(item) {
      this.product.tableData = this.product.tableData.filter(
        (row) => row !== item
      );
      this.product.size = this.product.size.filter(
        (size) => size !== item.size
      );
    },

    handleFileUpload(event) {
      const files = Array.from(event.target.files);
      const remainingSlots = 10 - this.product.uploadedImages.length;

      if (files.length > remainingSlots) {
        alert(`You can only add ${remainingSlots} more image(s).`);
      }

      files.slice(0, remainingSlots).forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
          this.product.uploadedImages.push(e.target.result); // Add each image to the array
        };
        reader.readAsDataURL(file);
      });
    },
    removeImage(index) {
      this.product.uploadedImages.splice(index, 1); // Remove image by index
    },

    fetchCategories() {
      axios.get("/getCategoryOption").then((response) => {
        this.categories = response.data.data;
      });
    },
    selectCategory(category) {
      this.selectedCategoryId = category.id;
      this.product.category = category.id;
      this.selectedSubcategoryId = null; // Reset subcategory selection
      this.product.sub_category = null;
      this.product.uploadedImages = [];
    },
    selectSubcategory(subcategory) {
      this.selectedSubcategoryId = subcategory.id;
      this.product.sub_category = subcategory.id;
      this.product.uploadedImages = [];
    },
    fromSubmit() {
      axios
        .post("/products", this.product)
        .then((response) => {
          if (response.data.status == "success") {
            toast("Product Added Successful!", { autoClose: 3000, type: "success" });
            this.$router.push({ name: "product" });
          }
        })
        .catch((error) => {
          if (error.response.data.code == 422) {
            this.errors = error.response.data.errors;
          }
        });
    },

    removeError(field) {
    if (this.errors[field]) {
      delete this.errors[field];
    }
  },
    hasErrors(fieldName) {
      return this.errors && this.errors[fieldName];
    },
    getErrors(fieldName) {
      return this.errors[fieldName] ? this.errors[fieldName][0] : '';
    },
   
  },
};
</script>


<style scoped>
.file-upload-container {
  display: flex;
  flex-wrap: wrap; /* Allow images to wrap to the next line */
  gap: 20px; /* Space between images and input box */
  align-items: center;
  margin-top: 10px;
}

.uploaded-image-container {
  position: relative;
  display: inline-block;
  border: 1px dashed #9a9a9a;
  border-radius: 8px;
  width: 150px;
  height: 150px;
  background-color: #f7f9fc;
  text-align: center;
  overflow: hidden;
}

.uploaded-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.close-icon {
  position: absolute;
  top: 5px;
  right: 5px;
  background: #fff;
  border: 1px solid #ddd;
  border-radius: 50%;
  font-size: 16px;
  line-height: 16px;
  cursor: pointer;
  width: 24px;
  height: 24px;
  text-align: center;
}

.custom-file-input {
  display: inline-block;
  cursor: pointer;
  text-align: center;
  border: 1px dashed #9a9a9a;
  border-radius: 8px;
  background-color: #f7f9fc;
  padding: 20px;
  width: 150px;
  height: 150px;
  transition: border-color 0.3s;
}

.custom-file-input:hover {
  border-color: #673ab7;
}

.file-input-box {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  height: 100%;
}

.icon {
  font-size: 24px;
  color: #673ab7;
  font-weight: bold;
}

.text {
  margin-top: 8px;
  font-size: 14px;
  color: #673ab7;
}

.list-group-item.active {
  background-color: #007bff;
  color: white;
  font-weight: bold;
}

/* Add scrollbar styling */
.list-container {
  max-height: 400px;
  overflow-y: auto;
}
.dashboard {
  padding: 20px;
  min-height: 100vh;
  width: 100%;
}
</style>
