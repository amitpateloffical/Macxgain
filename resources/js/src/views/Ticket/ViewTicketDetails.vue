<template>
    <div class="view_details_screen">
        <div class="title_line_screen px-4 py-2">
            <div class="title_line_left">
                <h5>#TC-190 | Website Outage</h5>
            </div>
            <div class="title_line_right">
                <b-button class="btn_secondary_border">Back</b-button>
            </div>
        </div>
        <div class="view_page_divider">
            <div class="view_divider_details px-4">
                <div class="view_divider_list">
                    <div class="view_divider_list_items">
                        <span>Priority:</span>
                        <span class="view_priority_badge badge_medium">{{fetchData.shortData.priority}}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Ticket Type:</span>
                        <span>{{fetchData.shortData.ticketType}}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Requester/Client:</span>
                        <span>{{fetchData.shortData.RequesterClient}}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Assignee:</span>
                        <span>{{fetchData.shortData.assignee}}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Tags:</span>
                        <span>{{fetchData.shortData.tags}}</span>
                    </div>
                    <div class="view_divider_list_items">
                        <span>Followers:</span>
                        <span>{{fetchData.shortData.followers}}</span>
                    </div>
                </div>
                <div class="view_divider_message">
                    <h4>Message</h4>
                    <span>From</span>
                    <p>{{fetchData.messageData.from}}</p>
                    <span>Message</span>
                    <p>{{fetchData.messageData.message}}</p>
                </div>
            </div>
            <div class="view_divider_chats_status_info px-4">
                <b-tabs>
                    <b-tab>
                        <template #title>
                            <span>
                                <ConversationIcon /> Conversation
                            </span>
                        </template>
                        <div class="tabs_view">
                            <div class="converstaion_list pt-4" v-if="fetchData.tabsData.conversation.chatData.length">
                                <div class="converstaion_list_item converstaion_list_time">
                                    <span>{{ fetchData.tabsData.conversation.date }}</span>
                                </div>
                                <div class="converstaion_list_item" v-for="(tablistdata, tablistindex) in fetchData.tabsData.conversation.chatData" :key="tablistindex" :class="[tablistdata.userStatus == 'sender' ? 'converstaion_list_item_sender' : 'converstaion_list_item_receiver']">
                                    <div class="converstaion_list_item_short_name">{{ getshortName(tablistdata.name) }}</div>
                                    <div class="converstaion_list_item_details">
                                        <span class="conversation_list_name">{{ tablistdata.name }} | {{ tablistdata.loginStatus}} </span>
                                        <p class="conversation_list_topic">{{tablistdata.title}}</p>
                                        <p class="conversation_list_message">{{ tablistdata.chatDescription }}</p>
                                        <span class="conversation_list_datetime">{{ tablistdata.date }}</span>
                                    </div>
                                </div>                                
                            </div>
                            <div class="no_data_found" v-else>
                                <div>
                                    <img src="../assest/img/no_conversation.png" alt="no conversation">
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
                                            <label for="">Via</label> <v-select :options="chatMessageData.via"
                                                value="label"></v-select>
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
                    <b-tab>
                        <template #title>
                            <span>
                                <TaskIcon /> Task
                            </span>
                        </template>
                        <div class="tabs_view">
                            <div class="task_view py-4 px-2" v-if="fetchData.tabsData.task.taskData.length">
                                <div class="timeline_view grey_timeline">
                                    <span class="timeline_view_date">{{ fetchData.tabsData.task.date }}</span>
                                    <ul>
                                        <li v-for="(taskdata, taskindex) in fetchData.tabsData.task.taskData" :key="taskindex">
                                            <h5>{{taskdata.title}}</h5>
                                            <p>{{taskdata.taskDescription}}</p>
                                            <span>{{taskdata.datetime}}</span>
                                        </li>                                        
                                    </ul>
                                </div>
                            </div>
                            <div class="no_data_found" v-else>
                                <div>
                                    <img src="../assest/img/no_task.png" alt="no conversation">
                                    <p>No Tasks Assigned</p>
                                    <span>There are currently no tasks associated </span>
                                    <span>with this ticket.</span>
                                    <b-link>+ Add New Task</b-link>
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
                                        <li v-for="(logdata, logindex) in fetchData.tabsData.logs.logData" :key="logindex">
                                            <h5>{{logdata.title}}</h5>
                                            <p>{{logdata.logDescription}}</p>
                                            <span>{{logdata.datetime}}</span>
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
                                <div class="converstaion_list_item" v-for="(noteslistdata, noteindex) in fetchData.tabsData.notes.notesData" :key="noteindex" :class="[noteslistdata.userStatus == 'sender' ? 'converstaion_list_item_sender' : 'converstaion_list_item_receiver']">
                                    <div class="converstaion_list_item_short_name">{{ getshortName(noteslistdata.name) }}</div>
                                    <div class="converstaion_list_item_details">
                                        <span class="conversation_list_name">{{ noteslistdata.name }} | {{ noteslistdata.loginStatus}} </span>
                                        <p class="conversation_list_topic">{{noteslistdata.title}}</p>
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
import ConversationIcon from '../assest/img/icons/Conversation.vue';
import TaskIcon from '../assest/img/icons/Task.vue';
import ActivityLogsIcon from '../assest/img/icons/ActivityLogs.vue';
import NotesIcon from '../assest/img/icons/Notes.vue';

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
    shortData:{
        name: '#TC-190 | Website Outage',
        priority: 'medium',
        ticketType: 'Task',
        RequesterClient: 'Charlie Black',
        assignee: 'Kevin Adams',
        tags: 'Report, Task, Monthly',
        followers: 'Helen White'        
    },
    messageData:{
        from: 'Technical Support Team',
        message: 'Our client ABC Corp is experiencing issues with their website taking too long to load. Please investigate the issue and provide a solution.'
    },
    tabsData: {
        conversation: {
            date: 'SEPTEMBER 2024',
            chatData: [
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
        task:{
            date: 'SEPTEMBER 2024',
            taskData: [
                {
                    title: 'Notify Client',
                    taskDescription: 'The website is now operational. Please check and confirm.',
                    datetime: 'September 20, 2024, 10:50 AM'
                },
                {
                    title: 'Resolve Outage',
                    taskDescription: 'Identified and fixed the server issue. Website is back online.',
                    datetime: 'September 20, 2024, 10:45 AM'
                }
            ]
        },
        logs:{
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
