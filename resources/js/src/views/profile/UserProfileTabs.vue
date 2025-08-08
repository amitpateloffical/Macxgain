<template>
    <b-container fluid class="main-content">
      <b-row>
        <b-col>
          <b-tabs pills content-class="mt-3" nav-class="justify-content-center">
            <b-tab title="General" active>
              <GeneralTab :userId="userId" />
            </b-tab>
            <b-tab title="Change Password">
              <ChangePasswordTab :userId="userId" />
            </b-tab>
          </b-tabs>
        </b-col>
      </b-row>
    </b-container>
  </template>
  
  <script>
  import GeneralTab from './GeneralTab.vue'; 
  import ChangePasswordTab from './ChangePasswordTab.vue'; 
  
  export default {
    props: {
      userId: {
        type: String,
        required: true
      }
    },
    components: {
      GeneralTab,
      ChangePasswordTab
    }
  };
  </script>
  