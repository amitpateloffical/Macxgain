<template>
	<div class="sidebar_screen">
		<div class="sidebar_item">
			<router-link to="/admin/dashboard" class="button"><DashboardIcon /> <span class="tooltiptext">Dashboard</span></router-link>
		</div>
		<div class="sidebar_item">
			<router-link to="/admin/product" class="button"><InboxIcon /> <span class="tooltiptext">Product</span></router-link>
		</div>
		<div class="sidebar_item">
			<router-link to="/outlook" class="button"><InboxIcon /> <span class="tooltiptext">Inbox</span></router-link>
		</div>
		<div class="sidebar_item">
			<router-link to="/ticket" class="button"><TicketIcon /> <span class="tooltiptext">Ticket</span></router-link>
		</div>
		<div class="sidebar_item">
			<router-link to="/customers" class="button"><CustomerIcon /> <span class="tooltiptext">Customers</span></router-link>
		</div>
		<div class="sidebar_item">
			<KnowledgebaseIcon /> <span class="tooltiptext">Knowledge Base</span>
		</div>
		<div class="sidebar_item">
			<ForumIcon /> <span class="tooltiptext">Forum</span>
		</div>
		<div class="sidebar_item">
			<ReportIcon /> <span class="tooltiptext">Reports</span>
		</div>
	</div>
	
</template>

<script setup>
import { ref } from 'vue'
import Swal from 'sweetalert2';
import axios from "@axios";
import DashboardIcon from "../assest/img/sidebar/DashboardIcon.vue";
import InboxIcon from "../assest/img/sidebar/InboxIcon.vue";
import TicketIcon from "../assest/img/sidebar/TicketIcon.vue";
import CustomerIcon from '../assest/img/sidebar/CustomerIcon.vue';
import KnowledgebaseIcon from '../assest/img/sidebar/KnowledgebaseIcon.vue';
import ForumIcon from '../assest/img/sidebar/ForumIcon.vue';
import ReportIcon from '../assest/img/sidebar/ReportIcon.vue';

const is_expanded = ref(localStorage.getItem("is_expanded") === "true")

const ToggleMenu = () => {
	is_expanded.value = !is_expanded.value
	localStorage.setItem("is_expanded", is_expanded.value)
}
const logout = () => {
	axios.post("/logout").then(() => {
		localStorage.removeItem('userData')
		localStorage.removeItem('access_token')

		Swal.fire({
			title: 'Log Out Successful!',
			text: 'You will be redirected to the Login Page.',
			icon: 'success',
			timer: 2000,
			showConfirmButton: false
		})

		setTimeout(() => {
			window.location.href = '/login'
		}, 2000)
	}).catch((error) => {
		console.error('Logout error:', error)
		Swal.fire({
			title: 'Logout Failed!',
			text: 'Please try again.',
			icon: 'error'
		})
	})
}
</script>
