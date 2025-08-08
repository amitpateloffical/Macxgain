<template>
    <div class="view_details_screen">
        <div class="title_line_screen px-4 py-2">
            <div class="title_line_left">
                <h5>#CI-001 | ABC Corporation <span class="title_badge_active">Active</span></h5>
            </div>
            <div class="title_line_right">
                <b-button class="btn_secondary_border">Back</b-button>
            </div>
        </div>
        <div class="view_page_divider">
            <div class="view_divider_details">
                <div class="view_divider_list view_without_border_bottom view_border_bottom px-3">
                    <div class="view_edit_tool">
                        <b-link><img src="../assest/img/customer/edit.svg" alt="edit"></b-link>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Contact Person:</span>
                        <span>{{ fetchData.shortData.ticketType }}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Priority:</span>
                        <span class="view_priority_badge badge_medium">{{ fetchData.shortData.priority }}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span><img src="../assest/img/customer/mail.svg" alt="mail" /></span>
                        <span>{{ fetchData.shortData.RequesterClient }}</span>
                    </div>
                    <div class="view_divider_list_items mb-2">
                        <span><img src="../assest/img/customer/call.svg" alt="call" /></span>
                        <span>{{ fetchData.shortData.assignee }}</span>
                    </div>
                </div>
                <div class="view_divider_list view_border_bottom px-3">
                    <div class="view_edit_tool">
                        <b-link><img src="../assest/img/customer/edit.svg" alt="edit"></b-link>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Org. Name:</span>
                        <span>{{ fetchData.shortData.ticketType }}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Assigned To:</span>
                        <span class="view_priority_badge badge_medium">{{ fetchData.shortData.priority }}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Add. Line 1:</span>
                        <span>{{ fetchData.shortData.RequesterClient }}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Add. Line 2:</span>
                        <span>{{ fetchData.shortData.assignee }}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>State:</span>
                        <span>{{ fetchData.shortData.assignee }}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>City:</span>
                        <span>{{ fetchData.shortData.assignee }}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Country:</span>
                        <span>{{ fetchData.shortData.assignee }}</span>
                    </div>
                    <div class="view_divider_list_items mb-2">
                        <span>ZIP CODE:</span>
                        <span>{{ fetchData.shortData.assignee }}</span>
                    </div>
                </div>
                <div class="view_divider_message view_border_bottom px-3">
                    <h4>Description</h4>
                    <p>{{ fetchData.messageData.message }}</p>
                </div>
                <div class="view_divider_attachments px-3">
                    <h4>Attachments</h4>
                    <div class="view_attachments_list">
                        <div class="view_attachments_list_item">
                            <img src="../assest/img/customer/pdf.svg" alt="pdf">
                            <div class="attachment_item_name_size">
                                <p>Invoice.pdf</p>
                                <span>14MB</span>
                            </div>
                            <b-link><img src="../assest/img/customer/download.svg" alt="download"></b-link>
                            <b-link><img src="../assest/img/customer/more_menu.svg" alt="more_menu"></b-link>
                        </div>
                        <div class="view_attachments_list_item">
                            <img src="../assest/img/customer/doc.svg" alt="doc">
                            <div class="attachment_item_name_size">
                                <p>Invoice.pdf</p>
                                <span>14MB</span>
                            </div>
                            <b-link><img src="../assest/img/customer/download.svg" alt="download"></b-link>
                            <b-link><img src="../assest/img/customer/more_menu.svg" alt="more_menu"></b-link>
                        </div>
                    </div>
                </div>
            </div>
            <div class="view_divider_chats_status_info px-3">
                <b-tabs>
                    <b-tab>
                        <template #title>
                            <span>
                                <healthIcon /> Health
                            </span>
                        </template>
                        <div class="tabs_view">
                            <div class="tab_view_dashboard">
                                <div class="tab_view_kpi my-3">
                                    <div class="tab_view_kpi_item tab_view_kpi_item_green">
                                        <p>Health Score
                                            <GreenHeartIcon />
                                        </p>
                                        <h5>85</h5>
                                        <span>Healthy</span>
                                    </div>
                                    <div class="tab_view_kpi_item">
                                        <p>Churn Risk</p>
                                        <h5>5%</h5>
                                        <span>Low</span>
                                    </div>
                                    <div class="tab_view_kpi_item">
                                        <p>Next Renewal Date</p>
                                        <h5>12/15/2024</h5>
                                    </div>
                                    <div class="tab_view_kpi_item">
                                        <p>Contract Value</p>
                                        <h5>$500,000</h5>
                                    </div>
                                </div>
                                <h4>Health Score Breakdown</h4>
                                <div class="tab_view_kpi_wi">
                                    <div class="tab_view_kpi_wi_item">
                                        <p>Engagement Score <img src="../assest/img/customer/engagement_score.svg"
                                                alt="engagement_score"></p>
                                        <h5 class="kpi_wi_item_color_green">85</h5>
                                        <span>High Usage, 5 logins/week</span>
                                    </div>
                                    <div class="tab_view_kpi_wi_item">
                                        <p>Product Usage <img src="../assest/img/customer/product_usage.svg"
                                                alt="product_usage"></p>
                                        <h5>High engagement with core features</h5>
                                        <span>dashboard, reporting</span>
                                    </div>
                                    <div class="tab_view_kpi_wi_item">
                                        <p>Customer Satisfaction Score <img
                                                src="../assest/img/customer/customer_satisfaction_score.svg"
                                                alt="customer_satisfaction_score"></p>
                                        <h5>4.8/5</h5>
                                        <span>based on 2 surveys</span>
                                    </div>
                                </div>
                                <h4>Product Usage Analytics</h4>
                                <div class="tab_view_kpi_wi hs_pad">
                                    <div class="tab_view_kpi_wi_item wi_item_border_right">
                                        <h5>85</h5>
                                        <span>High Usage, 5 logins/week</span>
                                    </div>
                                    <div class="tab_view_kpi_wi_item wi_item_border_right">
                                        <h5>High engagement with core features</h5>
                                        <span>dashboard, reporting</span>
                                    </div>
                                    <div class="tab_view_kpi_wi_item">
                                        <h5>4.8/5</h5>
                                        <span>based on 2 surveys</span>
                                    </div>
                                </div>
                                <h4>Interaction Timeline</h4>
                                <div class="tab_view_table">
                                    <b-table striped bordered
                                        :items="fetchData.tabsData.health.interactionTimelineTableData"
                                        :fields="fetchData.tabsData.health.interactionTimelineTableFields"></b-table>
                                </div>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab>
                        <template #title>
                            <span>
                                <ProductServicesIcon /> Product/Service
                            </span>
                        </template>
                        <div class="tabs_view">
                            <div class="tab_view_table mt-3">
                                <b-table striped bordered
                                    :items="fetchData.tabsData.productServices.productServicesTableData"
                                    :fields="fetchData.tabsData.productServices.productServicesTableFields"></b-table>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab>
                        <template #title>
                            <span>
                                <InvoiceIcon /> Invoice
                            </span>
                        </template>
                        <div class="tabs_view">

                        </div>
                    </b-tab>
                    <b-tab>
                        <template #title>
                            <span>
                                <InvoiceIcon /> Ticket
                            </span>
                        </template>
                        <div class="tabs_view">
                            <div class="tabs_view_ticket tab_view_dashboard">
                                <div class="tab_view_kpi_wi hs_pad mt-3">
                                    <div class="tab_view_kpi_wi_item wi_item_border_right">
                                        <span>Tickets</span>
                                        <h5>16</h5>                                        
                                    </div>
                                    <div class="tab_view_kpi_wi_item wi_item_border_right">
                                        <span>Overdue ticket</span>
                                        <h5>4</h5>                                        
                                    </div>
                                    <div class="tab_view_kpi_wi_item">
                                        <span>AVG. Response time</span>
                                        <h5>25:00</h5> 
                                    </div>
                                    <div class="tab_view_kpi_wi_item">
                                        <span>Total response time</span>
                                        <h5>1:32:56</h5> 
                                    </div>
                                </div>
                                <div class="tab_view_table_borderd mt-4">
                                    <b-table :items="fetchData.tabsData.ticket.ticketTableData"
                                :fields="fetchData.tabsData.ticket.ticketTableFields"></b-table>
                                </div>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab>
                        <template #title>
                            <span>
                                <ActivityLogsIcon /> Activity Logs
                            </span>
                        </template>
                        <div class="tabs_view">
                            <div class="logs_view py-4 px-2" v-if="fetchData.tabsData.logs.logData.length">
                                <div class="timeline_view grey_timeline">
                                    <span class="timeline_view_date">{{ fetchData.tabsData.logs.date }}</span>
                                    <ul>
                                        <li v-for="(logdata, logindex) in fetchData.tabsData.logs.logData"
                                            :key="logindex">
                                            <h5>{{ logdata.title }}</h5>
                                            <p>{{ logdata.logDescription }}</p>
                                            <span>{{ logdata.datetime }}</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="no_data_found" v-else>
                                <div>
                                    <img src="../assest/img/no_logs.png" alt="no conversation">
                                    <p>No Activity Recorded</p>
                                    <span>There are currently no activities associated</span>
                                    <span>with this ticket.</span>
                                </div>
                            </div>
                        </div>
                    </b-tab>
                    <b-tab>
                        <template #title>
                            <span>
                                <NotesIcon /> Notes
                            </span>
                        </template>
                        <div class="tabs_view">
                            <div class="converstaion_list pt-4" v-if="fetchData.tabsData.notes.notesData.length">
                                <div class="converstaion_list_item converstaion_list_time">
                                    <span>{{ fetchData.tabsData.notes.date }}</span>
                                </div>
                                <div class="converstaion_list_item"
                                    v-for="(noteslistdata, noteindex) in fetchData.tabsData.notes.notesData"
                                    :key="noteindex"
                                    :class="[noteslistdata.userStatus == 'sender' ? 'converstaion_list_item_sender' : 'converstaion_list_item_receiver']">
                                    <div class="converstaion_list_item_short_name">{{ getshortName(noteslistdata.name)
                                        }}</div>
                                    <div class="converstaion_list_item_details">
                                        <span class="conversation_list_name">{{ noteslistdata.name }} | {{
                                            noteslistdata.loginStatus}}
                                        </span>
                                        <p class="conversation_list_topic">{{ noteslistdata.title }}</p>
                                        <p class="conversation_list_message">{{ noteslistdata.chatDescription }}</p>
                                        <span class="conversation_list_datetime">{{ noteslistdata.date }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="no_data_found" v-else>
                                <div>
                                    <img src="../assest/img/no_notes.png" alt="no conversation">
                                    <p>No Conversations Available</p>
                                    <span>Currently, there are no conversations or</span>
                                    <span>updates related to this ticket.</span>
                                </div>
                            </div>
                            <div class="conversation_chat_sytem">
                                <div class="converstaion_chat_sytem_short_name">SS</div>
                                <div class="conversation_system_message">
                                    <div class="conversation_system_textarea">
                                        <div class="conversation_system_textarea_items">
                                            <label for="">From</label> <v-select :options="chatMessageData.from"
                                                value="label"></v-select>
                                        </div>
                                        <b-form-textarea placeholder="Enter here..." rows="4"></b-form-textarea>
                                    </div>
                                    <div class="conversation_system_textarea_footer">
                                        <v-select></v-select>
                                        <b-button class="btn_primary">Send</b-button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </b-tab>
                </b-tabs>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import "vue-select/dist/vue-select.css";
import axios from "@axios";
import vSelect from "vue-select";
import healthIcon from '../assest/img/icons/Health.vue';
import ProductServicesIcon from '../assest/img/icons/ProductServices.vue';
import ActivityLogsIcon from '../assest/img/icons/ActivityLogs.vue';
import NotesIcon from '../assest/img/icons/Notes.vue';
import GreenHeartIcon from "../assest/img/icons/GreenHeart.vue";
import InvoiceIcon from "../assest/img/icons/Invoice.vue"

// Comman Variables

const chatMessageData = ref({
    via: [
        {
            key: "whats_app",
            label: "Whats'app"
        },
        {
            key: "messanger",
            label: "Messanger"
        }
    ],
    from: [
        {
            key: "arjun_patel",
            label: "Arjun Patel"
        },
        {
            key: "vishal_kotian",
            label: "Vishal Kotian"
        }
    ],
    ticket: [
        {
            key: "open_ticket",
            label: "open ticket"
        },
        {
            key: "close_ticket",
            label: "close ticket"
        }
    ],
})

//-----------------------------------------------------//


const fetchData = ref({
    shortData: {
        name: '#TC-190 | Website Outage',
        priority: 'medium',
        ticketType: 'Task',
        RequesterClient: 'Charlie Black',
        assignee: 'Kevin Adams',
        tags: 'Report, Task, Monthly',
        followers: 'Helen White'
    },
    messageData: {
        from: 'Technical Support Team',
        message: 'Our client ABC Corp is experiencing issues with their website taking too long to load. Please investigate the issue and provide a solution.'
    },
    tabsData: {
        health: {
            interactionTimelineTableData: [
                {
                    date: '09/15/2024',
                    interactionType: 'Meeting',
                    notes: 'Quarterly check-in meeting'
                },
                {
                    date: '09/15/2024',
                    interactionType: 'Meeting',
                    notes: 'Quarterly check-in meeting'
                },
                {
                    date: '09/15/2024',
                    interactionType: 'Meeting',
                    notes: 'Quarterly check-in meeting'
                }
            ],
            interactionTimelineTableFields: [
                { key: 'date', label: 'Date' },
                { key: 'interactionType', label: 'Interaction Type' },
                { key: 'notes', label: 'Notes' }
            ]
        },
        productServices: {
            productServicesTableData: [
                {
                    productServiceId: 'P-001',
                    productServiceName: 'Cloud Storage',
                    category: 'Product',
                    description: '500 GB cloud storage with h...',
                    priceRate: '$100/month',
                    subscriptionType: 'Subscription',
                    startDate: '1-Jan-2023',
                    endDate: '31-Dec-2023',
                    status: 'Active'
                },
                {
                    productServiceId: 'P-001',
                    productServiceName: 'Cloud Storage',
                    category: 'Product',
                    description: '500 GB cloud storage with h...',
                    priceRate: '$100/month',
                    subscriptionType: 'Subscription',
                    startDate: '1-Jan-2023',
                    endDate: '31-Dec-2023',
                    status: 'Active'
                },
                {
                    productServiceId: 'P-001',
                    productServiceName: 'Cloud Storage',
                    category: 'Product',
                    description: '500 GB cloud storage with h...',
                    priceRate: '$100/month',
                    subscriptionType: 'Subscription',
                    startDate: '1-Jan-2023',
                    endDate: '31-Dec-2023',
                    status: 'Active'
                }
            ],
            productServicesTableFields: [
                { key: 'productServiceId', label: 'Product Service ID' },
                { key: 'productServiceName', label: 'Product Service Name' },
                { key: 'category', label: 'Category' },
                { key: 'description', label: 'Description' },
                { key: 'priceRate', label: 'Price Rate' },
                { key: 'subscriptionType', label: 'Subscription Type' },
                { key: 'startDate', label: 'Start Date' },
                { key: 'endDate', label: 'End Date' },
                { key: 'status', label: 'Status' }
            ]
        },
        ticket:{
            ticketTableData:[],
            ticketTableFields:[],
        },
        logs: {
            date: 'SEPTEMBER 2024',
            logData: [
                {
                    title: 'Notify Client',
                    logDescription: 'The website is now operational. Please check and confirm.',
                    datetime: 'September 20, 2024, 10:50 AM'
                },
                {
                    title: 'Resolve Outage',
                    logDescription: 'Identified and fixed the server issue. Website is back online.',
                    datetime: 'September 20, 2024, 10:45 AM'
                }
            ]
        },
        notes: {
            date: 'SEPTEMBER 2024',
            notesData: [
                {
                    name: 'John Doe',
                    loginStatus: 'System',
                    title: 'Confirmation',
                    chatDescription: 'Thanks for the quick fix! The site is working again.',
                    date: 'Friday, 20, 10:15 AM',
                    userStatus: 'sender'
                },
                {
                    name: 'Sarah Smith',
                    loginStatus: 'System',
                    title: 'Update',
                    chatDescription: 'The website was down due to a server configuration issue. It’s now fixed and back online.',
                    date: 'Friday, 20, 10:50 AM',
                    userStatus: 'receiver'
                }
            ]
        },
    }
});

// ----------------------------------------------------------- //

const getshortName = (nameVal) => {
    const n = nameVal.split(" ");

    const firstword = nameVal[0];
    const lastword = n[n.length - 1].charAt(0);

    return firstword + lastword;
}


</script>
