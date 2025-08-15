<template>
    <div class="dashboard-health">
        <div class="kpi_group_screen mt-3">
            <div class="kpi_item">
                <span class="kpi_line1">Total Customers</span>
                <h4>500 <span class="kpi_badge kpi_red">-25%</span></h4>
                <span class="kpi_line2">UPTO LAST MONTH: 250</span>
            </div>
            <div class="kpi_item">
                <span class="kpi_line1">At-Risk Customers</span>
                <h4>120 <span class="kpi_badge kpi_green">-25%</span></h4>
                <span class="kpi_line2">UPTO LAST MONTH: 75</span>
            </div>
            <div class="kpi_item">
                <span class="kpi_line1">Churned Customers</span>
                <h4>60 <span class="kpi_badge kpi_green">-25%</span></h4>
                <span class="kpi_line2">UPTO LAST MONTH: 26</span>
            </div>
            <div class="kpi_item">
                <span class="kpi_line1">Customer Satisfaction Score</span>
                <h4>4.7/5 <span class="kpi_badge kpi_red">-25%</span></h4>
                <span class="kpi_line2">UPTO LAST MONTH: 4.2</span>
            </div>
        </div>
        <div class="graph_divider mt-4">
            <div class="divider50">
                <h6>Customer Health Breakdown</h6>
                <div class="graph_info">
                    <v-chart v-if="chbData.showstatus" class="chart_container" :option="chbData.options" autoresize />
                </div>
            </div>
            <div class="divider50">
                <h6>customer health scores segmented by factors</h6>
                <div class="graph_info">
                    <v-chart v-if="chssbfData.showstatus" class="chart_container" :option="chssbfData.options"
                        autoresize />
                </div>
            </div>
        </div>
        <div class="graph_divider mt-4">
            <div class="divider50">
                <h6>Engagement Over Time</h6>
                <div class="graph_info">
                    <div class="graph_tool">
                        <div class="graph_data_info">
                            <ul>
                                <li>
                                    <span>Engagement Over Time on daily activity login</span>
                                    <h5>last 90 days</h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <v-chart v-if="eotData.showstatus" class="chart_container" :option="eotData.options" autoresize />
                </div>
            </div>
            <div class="divider50">
                <h6>Satisfaction Score</h6>
                <div class="graph_info">
                    <div class="graph_tool">
                        <div class="graph_data_info">
                            <ul>
                                <li>
                                    <span>Over All Customer Score</span>
                                    <h5>60</h5>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <v-chart v-if="ssData.showstatus" class="chart_container" :option="ssData.options" autoresize />
                </div>
            </div>
        </div>
        <div class="table_format mt-4">
            <div class="title_divider">
                <h6>Latest 10 Tickets</h6>
                <b-link>View All</b-link>
            </div>
            <div class="table-responsive">
                <b-table :items="ltData.tabledata" :fields="ltData.ChartField" responsive>
                    <template #cell(priority)="data">
                        <span v-if="data.value == 'High'" class="badgehigh">{{ data.value }}</span>
                        <span v-if="data.value == 'Medium'" class="badgemedium">{{ data.value }}</span>
                        <span v-if="data.value == 'Low'" class="badgelow">{{ data.value }}</span>
                    </template>
                    <template #cell(client)="data">
                        <span class="table_img_cell"><span class="table_img"><img
                                    src="./assest/img/tableprofileimg.png" alt="profile"/></span>{{
                                        data.value }}</span>
                    </template>
                    <template #cell(action)>
                        <div class="grid_table_action"><i class="fa-solid fa-ellipsis"></i></div>
                    </template>
                </b-table>
            </div>
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
const chbData = ref({
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

const chssbfData = ref({
    options: {
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'shadow'
            }
        },
        legend: {},
        xAxis: {
            type: 'category',
            data: []
        },
        yAxis: {
            type: 'value',
            boundaryGap: [0, 0.01]
        },
        grid: {
            left: '8%',
            top: '20%',
            right: '1%',
            bottom: '10%'
        },
        series: []
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

const eotData = ref({
    options: {
        xAxis: {
            type: 'category',
            data: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
        },
        yAxis: {
            type: 'value'
        },
        grid: {
            left: '5%',
            top: '5%',
            right: '1%',
            bottom: '20%'
        },
        series: [
            {
                data: [],
                type: 'line',
                symbolSize: 2,
                lineStyle: {
                    normal: {
                        color: '#4D5BA4',
                        width: 1,
                    }
                }
            }
        ]
    },
    tabledata: [],
    ChartField: [
        { key: "name", sortable: true },
        { key: "value", sortable: true },
    ],
    showstatus: true,
    loader: true
});

const ssData = ref({
    options: {
        tooltip: {
            trigger: 'axis'
        },
        legend: {},
        xAxis: {
            type: 'category',
            boundaryGap: false,
            data: []
        },
        yAxis: {
            type: 'value',
            axisLabel: {
                formatter: '{value} °C'
            }
        },
        grid: {
            left: '5%',
            top: '6%',
            right: '1%',
            bottom: '20%'
        },
        series: [
            {
                name: 'Highest',
                type: 'line',
                data: [],
                markPoint: {
                    data: [
                        { type: 'max', name: 'Max' },
                        { type: 'min', name: 'Min' }
                    ]
                },
                markLine: {
                    data: [{ type: 'average', name: 'Avg' }]
                }
            }
        ]
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
    chbChart();
    chssbfChart();
    eotChart();
    ssChart();
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

const chbChart = () => {
    chbData.value.loader = true;

    chbData.value.options.series[0].data = [
        { value: 1048, name: 'Search Engine' },
        { value: 735, name: 'Direct' },
        { value: 580, name: 'Email' },
        { value: 484, name: 'Union Ads' },
        { value: 300, name: 'Video Ads' }
    ];
    chbData.value.tabledata = [];

    chbData.value.loader = false;
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

const chssbfChart = () => {
    chssbfData.value.loader = true;


    chssbfData.value.options.xAxis.data = ['Brazil', 'Indonesia', 'USA', 'India', 'China', 'World'];
    chssbfData.value.options.series = [
        {
            name: '2011',
            type: 'bar',
            data: [18203, 23489, 29034, 104970, 131744, 630230]
        },
        {
            name: '2012',
            type: 'bar',
            data: [19325, 23438, 31000, 121594, 134141, 681807]
        },
        {
            name: '2013',
            type: 'bar',
            data: [19325, 23438, 31000, 121594, 134141, 681807]
        }
    ];
    chssbfData.value.tabledata = [];

    chssbfData.value.loader = false;
    // axios
    //   .post(`/atc`, {
    //     filters: [],
    //   })
    //   .then((response) => {


    //     
    //   })
    //   .catch((error) => {
    //     console.log(error);
    //   });
};

const eotChart = () => {
    eotData.value.loader = true;

    eotData.value.options.series[0].data = [150, 230, 224, 218, 135, 147, 260, 150, 230, 224, 218, 135, 147, 260];
    eotData.value.tabledata = [];

    eotData.value.loader = false;
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

const ssChart = () => {
    ssData.value.loader = true;

    ssData.value.options.xAxis.data = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    ssData.value.options.series[0].data = [10, 11, 13, 11, 12, 12, 9];
    ssData.value.tabledata = [];

    ssData.value.loader = false;
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