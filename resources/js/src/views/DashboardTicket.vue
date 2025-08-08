<template>
  <div>
    <div class="kpi_group_screen mt-3">
      <div class="kpi_item">
        <span class="kpi_line1">Created Tickets</span>
        <h4>23,569 <span class="kpi_badge kpi_red">-25%</span></h4>
        <span class="kpi_line2">Compare to last month</span>
      </div>
      <div class="kpi_item">
        <span class="kpi_line1">Unsolved Tickets</span>
        <h4>23,569 <span class="kpi_badge kpi_green">-25%</span></h4>
        <span class="kpi_line2">Compare to last month</span>
      </div>
      <div class="kpi_item">
        <span class="kpi_line1">Solved Tickets</span>
        <h4>23,569 <span class="kpi_badge kpi_green">-25%</span></h4>
        <span class="kpi_line2">Compare to last month</span>
      </div>
      <div class="kpi_item">
        <span class="kpi_line1">Average First Time Reply</span>
        <h4>12:01 min <span class="kpi_badge kpi_red">-25%</span></h4>
        <span class="kpi_line2">Compare to last month</span>
      </div>
    </div>
    <div class="graph_divider mt-4">
      <div class="divider60">
        <h6>Average Tickets Created</h6>
        <div class="graph_info">
          <div class="graph_tool">
            <div class="graph_data_info">
              <ul>
                <li>
                  <span>Avg. tickets created</span>
                  <h5>3,456</h5>
                </li>
                <li>
                  <span>Avg. tickets Solved</span>
                  <h5>3,120</h5>
                </li>
              </ul>
            </div>
            <v-select :options="['Day', 'Week', 'Month', 'Year']" placeholder="Select" clearable="false"></v-select>
          </div>
          <v-chart v-if="atcData.showstatus" class="chart_container" :option="atcData.options" autoresize />
        </div>
      </div>
      <div class="divider40">
        <h6>Ticket By First Reply Time</h6>
        <div class="graph_info">
          <v-chart v-if="tbfrtData.showstatus" class="chart_container" :option="tbfrtData.options" autoresize />
        </div>
      </div>
    </div>
    <div class="graph_divider mt-4">
      <div class="divider50">
        <h6>Tickets By Channels</h6>
        <div class="graph_info">
          <div class="graph_tool">
            <div class="graph_data_info">
              <ul>
                <li>
                  <span>Total Active Tickets</span>
                  <h5>2,659</h5>
                </li>
              </ul>
            </div>
          </div>
          <v-chart v-if="tbcData.showstatus" class="chart_container" :option="tbcData.options" autoresize />
        </div>
      </div>
      <div class="divider50">
        <h6>Customer Satisfaction</h6>
        <div class="graph_info grid_flex_items">
          <div class="graph_grid_item">
            <span>responsive Request</span>
            <h4>156 Customer</h4>
          </div>
          <div class="graph_grid_item">
            <div class="grid_item_divider">
              <div>
                <span>Positive</span>
                <h4>80%</h4>
              </div>
              <thumbup />
            </div>
            <div class="graph_range">
              <div class="graph_range_bar">
                <div class="graph_range_width graph_range_green"></div>
              </div>
              <span class="graph_range_percent">24%</span>
            </div>
          </div>
          <div class="graph_grid_item">
            <div class="grid_item_divider">
              <div>
                <span>Neutral</span>
                <h4>80%</h4>
              </div>
              <minus />
            </div>
            <div class="graph_range">
              <div class="graph_range_bar">
                <div class="graph_range_width graph_range_orange"></div>
              </div>
              <span class="graph_range_percent">24%</span>
            </div>
          </div>
          <div class="graph_grid_item">
            <div class="grid_item_divider">
              <div>
                <span>Negative</span>
                <h4>80%</h4>
              </div>
              <thumbdown />
            </div>
            <div class="graph_range">
              <div class="graph_range_bar">
                <div class="graph_range_width graph_range_red"></div>
              </div>
              <span class="graph_range_percent">24%</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="table_format mt-4">
      <div class="title_divider">
        <h6>Latest 10 Tickets</h6>
        <b-link>View All</b-link>
      </div>
      <b-table :items="ltData.tabledata" :fields="ltData.ChartField">
        <template #cell(priority)="data">
          <span v-if="data.value == 'High'" class="badgehigh">{{ data.value }}</span>
          <span v-if="data.value == 'Medium'" class="badgemedium">{{ data.value }}</span>
          <span v-if="data.value == 'Low'" class="badgelow">{{ data.value }}</span>
        </template>
        <template #cell(client)="data">
          <span class="table_img_cell"><span class="table_img"><img src="./assest/img/tableprofileimg.png" alt="profile" /></span>{{
            data.value }}</span>
        </template>
        <template #cell(action)="data">
          <div class="grid_table_action"><i class="fa-solid fa-ellipsis"></i></div>
        </template>
      </b-table>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import axios from 'axios';
import * as echarts from "echarts";
import VChart from 'vue-echarts';
import "vue-select/dist/vue-select.css";
import vSelect from "vue-select";
import thumbup from '../views/assest/img/icons/Thumbup.vue';
import thumbdown from '../views/assest/img/icons/Thumbdown.vue';
import minus from '../views/assest/img/icons/Minus.vue';

// Comman variable

//-----------------------------------------------------------------------------------//

// graph variables
const atcData = ref({
  options: {
    legend: {
      show: true,
      selectedMode: true,
    },
    grid: {
      left: 100,
      right: 100,
      top: 50,
      bottom: 50
    },
    yAxis: {
      type: "value",
    },
    xAxis: {
      type: "category",
      data: [],
    },
    tooltip: {
      trigger: "axis",
      formatter: (params) => {
        let tooltipText = `${params[0].axisValue}<br/>`;
        params.forEach((param) => {
          const percentage = Math.round(param.value * 1000) / 10; // Calculate percentage
          tooltipText += `${param.marker} ${param.seriesName}: ${percentage}%<br/>`;
        });
        return tooltipText;
      },
    },
    series: [],
    // dataZoom: [
    //   {
    //     id: "dataZoomX",
    //     type: "slider",
    //     xAxisIndex: [0],
    //     filterMode: "filter",
    //     height: 10,
    //     bottom: 10
    //   },
    //   {
    //     id: "dataZoomY",
    //     type: "slider",
    //     yAxisIndex: [0],
    //     filterMode: "empty",
    //     width: 10,
    //   },
    // ],
  },
  tabledata: [],
  ChartField: [
    { key: "supplier", sortable: true },
    { key: "vouchered", sortable: true },
    { key: "cancelled", sortable: true },
    { key: "failed", sortable: true },
    { key: "rejected", sortable: true },
    { key: "others", sortable: true },
    { key: "confirmed", sortable: true },
  ],
  showstatus: true,
  loader: true
});

const tbfrtData = ref({
  options: {
    tooltip: {
      trigger: 'item'
    },
    legend: {
      orient: 'vertical',
      right: 'right',
      top: '50%'
    },
    series: [
      {
        name: 'Access From',
        type: 'pie',
        radius: '50%',
        center: ['35%', '70%'],
        width: "100%",
        height: "100%",
        data: [],
        emphasis: {
          itemStyle: {
            shadowBlur: 10,
            shadowOffsetX: 0,
            shadowColor: 'rgba(0, 0, 0, 0.5)'
          }
        }
      }
    ],
  },
  tabledata: [],
  ChartField: [
    { key: "name", sortable: true },
    { key: "value", sortable: true },
  ],
  showstatus: true,
  loader: true
});

const tbcData = ref({
  options: {
    tooltip: {
      trigger: "item",
    },
    legend: {
      bottom: '20%',
      left: 'center'
    },
    series: [
      {
        name: 'Access From',
        type: 'pie',
        radius: ['62%', '70%'],
        center: ['50%', '50%'],
        itemStyle: {
          borderRadius: 4,
          borderColor: '#fff',
          borderWidth: 2
        },
        // adjust the start and end angle
        startAngle: 180,
        endAngle: 360,
      },
    ],
  },
  tabledata: [],
  ChartField: [
    { key: "name", sortable: true },
    { key: "value", sortable: true },
  ],
  showstatus: true,
  loader: true
});

const ltData = ref({
  tabledata: [],
  ChartField: [
    { key: "ticket_ID", sortable: true },
    { key: "subject", sortable: true },
    { key: "priority", sortable: true },
    { key: "Type", sortable: true },
    { key: "client", sortable: true },
    { key: "request_date", sortable: true },
    { key: "action", sortable: false },
  ],
  showstatus: true,
  loader: true
});



//-----------------------------------------------------------------------------------//
// load all functions
const fetchAllData = () => {
  loadkpi();
  atcChart();
  tbcChart();
  tbfrtChart();
  ltTable();
}

//-----------------------------------------------------------------------------------//

// load all kpi

const kpiData = ref({
  bookingData: null,
  salesData: null,
  SearchCount: null,
  ProLossData: null,
  CurrencyData: [],
  SerachMinDate: null,
  SearchMMaxDate: null,
  BookingMinDate: null,
  BookingMMaxDate: null,
  WhitelabelBooking: null,
  WhitelabelProfit: null
})

const loadkpi = () => {

  // // Created Tickets
  // axios
  //   .post(`${hostName}/clientssaleskpi`, { filters: filterData.value })
  //   .then((response) => {
  //     kpiData.value.salesData = response.data.values[0];
  //   });

  // // Unsolved Tickets
  // axios
  //   .post(`${hostName}/clientsbookingskpi`, { filters: filterData.value })
  //   .then((response) => {
  //     kpiData.value.bookingData = response.data.values[0];
  //   });

  // // Solved Tickets
  // axios
  //   .post(`${hostName}/clientsprofitlosskpi`, { filters: filterData.value })
  //   .then((response) => {
  //     kpiData.value.ProLossData = response.data.values[0];
  //   });

  // // Average First Time Reply
  // axios
  //   .post(`${hostName}/clientsprofitlosskpi`, { filters: filterData.value })
  //   .then((response) => {
  //     kpiData.value.ProLossData = response.data.values[0];
  //   });

}

// load graph functions

const atcChart = () => {
  atcData.value.loader = true;

  const rawData = [
    [100, 302, 301, 334, 390, 330, 320],
    [320, 132, 101, 134, 90, 230, 210]
  ];
  const totalData = [];

  for (let i = 0; i < rawData[0].length; i++) {
    let sum = 0;
    for (let j = 0; j < rawData.length; j++) {
      sum += rawData[j][i];
    }
    totalData.push(sum);
  }


  const series = [
    'Direct',
    'Mail Ad'
  ].map((name, sid) => {
    return {
      name,
      type: "bar",
      stack: "total",
      barWidth: "60%",
      label: {
        show: true,
        formatter: (params) => Math.round(params.value * 1000) / 10 + "%",
      },
      data: rawData[sid].map((d, did) =>
        totalData[did] <= 0 ? 0 : d / totalData[did]
      ),
    };
  });

  atcData.value.options.xAxis.data = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
  atcData.value.options.series = series;
  atcData.value.tabledata = [];

  atcData.value.loader = false;
  // axios
  //   .post(`/atc`, {
  //     filters: [],
  //   })
  //   .then((response) => {

  //     const rawData = [
  //       [100, 302, 301, 334, 390, 330, 320],
  //       [320, 132, 101, 134, 90, 230, 210]
  //     ];
  //     const totalData = [];

  //     for (let i = 0; i < rawData[0].length; i++) {
  //       let sum = 0;
  //       for (let j = 0; j < rawData.length; j++) {
  //         sum += rawData[j][i];
  //       }
  //       totalData.push(sum);
  //     }


  //     const series = [
  //       'Direct',
  //       'Mail Ad'
  //     ].map((name, sid) => {
  //       return {
  //         name,
  //         type: "bar",
  //         stack: "total",
  //         barWidth: "60%",
  //         label: {
  //           show: false,
  //           formatter: (params) => Math.round(params.value * 1000) / 10 + "%",
  //         },
  //         data: rawData[sid].map((d, did) =>
  //           totalData[did] <= 0 ? 0 : d / totalData[did]
  //         ),
  //       };
  //     });

  //     atcData.value.options.xAxis.data = ["abc","def"];
  //     atcData.value.options.series = series;
  //     atcData.value.tabledata = [];

  //     atcData.value.loader = false;
  //   })
  //   .catch((error) => {
  //     console.log(error);
  //   });
};

const tbcChart = () => {
  tbcData.value.loader = true;

  tbcData.value.options.series[0].data = [
    { value: 1048, name: 'Search Engine' },
    { value: 735, name: 'Direct' },
    { value: 580, name: 'Email' },
    { value: 484, name: 'Union Ads' },
    { value: 300, name: 'Video Ads' }
  ];
  tbcData.value.tabledata = [];

  tbcData.value.loader = false;
  // axios
  //     .post(`${hostName}/clientsservicetype`, {
  //         filters: filterData.value,
  //     })
  //     .then((response) => {
  //         stData.value.options.series[0].data = response.data.data;
  //         stData.value.tabledata = response.data.data;

  //         stData.value.loader = false;
  //     })
  //     .catch((error) => {
  //         console.log(error);
  //     });
};

const tbfrtChart = () => {
  tbfrtData.value.loader = true;

  tbfrtData.value.options.series[0].data = [
    { value: 1048, name: 'Search Engine' },
    { value: 735, name: 'Direct' },
    { value: 580, name: 'Email' },
    { value: 484, name: 'Union Ads' },
    { value: 300, name: 'Video Ads' }
  ];
  tbfrtData.value.tabledata = [];

  tbfrtData.value.loader = false;
  // axios
  //     .post(`${hostName}/clientsservicetype`, {
  //         filters: filterData.value,
  //     })
  //     .then((response) => {
  //         stData.value.options.series[0].data = response.data.data;
  //         stData.value.tabledata = response.data.data;

  //         stData.value.loader = false;
  //     })
  //     .catch((error) => {
  //         console.log(error);
  //     });
}

const ltTable = () => {

  ltData.value.tabledata = [
    { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'High', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
    { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'High', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
    { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'Medium', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
    { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'Low', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
    { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'High', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
    { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'Medium', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
    { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'High', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
    { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'Low', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
    { ticket_ID: '#TC_190', subject: 'API integration failure affecting client apps', priority: 'High', Type: 'Incident', client: 'Alex Johnson', request_date: '8/9/2024, 10:29 AM' },
  ];
  // axios
  //     .post(`${hostName}/clientsservicetype`, {
  //         filters: filterData.value,
  //     })
  //     .then((response) => {
  //         stData.value.options.series[0].data = response.data.data;
  //         stData.value.tabledata = response.data.data;

  //         stData.value.loader = false;
  //     })
  //     .catch((error) => {
  //         console.log(error);
  //     });
}




//-----------------------------------------------------------------------------------//


// load all data
fetchAllData();

// Zoom tab data
const zoomtabgt = (evt) => {
  evt.target.parentElement.parentElement.parentElement.parentElement.classList.toggle(
    "zoomgt"
  );
};


</script>