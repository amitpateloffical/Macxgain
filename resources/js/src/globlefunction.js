
export const func = {
    encodeBase64: (data) => {
        return data == null? data : btoa(data.toString());
    },
    decodeBase64: (data) => {
        return data == null? data :atob(data)
    },
  
    formatDate: (dateString) => {
      const date = new Date(dateString);
      const day = date.getDate().toString().padStart(2, "0");
      const month = date.toLocaleString("default", { month: "short" });
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    },
    formatTime: (timeString) => {
      const [hourStr, minuteStr] = timeString.split(":");
      let hours = parseInt(hourStr, 10);
      const minutes = minuteStr.padStart(2, "0");
      const ampm = hours >= 12 ? "PM" : "AM";
      hours = hours % 12 || 12;
      return `${hours}:${minutes} ${ampm}`;
    },
    formatDateTime: (dateString) => {
      const date = new Date(dateString); 
      const day = date.getDate().toString().padStart(2, "0"); 
      const month = date.toLocaleString("default", { month: "short" }); 
      const year = date.getFullYear(); 
      let hours = date.getHours(); 
      const minutes = date.getMinutes().toString().padStart(2, "0"); 
      const ampm = hours >= 12 ? "PM" : "AM";
      hours = hours % 12 || 12; 
      return `${day}/${month}/${year} ${hours}:${minutes}${ampm}`;
    },
    selectDateTime: () => {
      const currentDate = new Date();
      return `${currentDate.getFullYear()}-${(currentDate.getMonth() + 1)
      .toString()
      .padStart(2, "0")}-${currentDate
      .getDate()
      .toString()
      .padStart(2, "0")}T${currentDate
      .getHours()
      .toString()
      .padStart(2, "0")}:${currentDate
      .getMinutes()
      .toString()
      .padStart(2, "0")}`;
    },
    selectDate: () => {
      const currentDate = new Date();
      return `${currentDate.getFullYear()}-${(currentDate.getMonth() + 1)
        .toString()
        .padStart(2, "0")}-${currentDate
        .getDate()
        .toString()
        .padStart(2, "0")}`;
    },
    minDateTime: () => {
      const currentDate = new Date();
      const year = currentDate.getFullYear();
      const month = String(currentDate.getMonth() + 1).padStart(2, "0");
      const day = String(currentDate.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}T00:00`;
    },
    maxDateTime: () => {
      const currentDate = new Date();
      const year = currentDate.getFullYear();
      const month = String(currentDate.getMonth() + 1).padStart(2, "0");
      const day = String(currentDate.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}T23:59`;
    },     
    maxDate: () => {
      const today = new Date();
      const year = today.getFullYear() - 18;
      const month = String(today.getMonth() + 1).padStart(2, '0');
      const day = String(today.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },
    minDate: () => {
      const today = new Date();
      const year = today.getFullYear() - 100; // 100 years ago
      const month = String(today.getMonth() + 1).padStart(2, '0');
      const day = String(today.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    },    
    capitalizeWords: () => {
      if (string == null || string == "") {
        return "-";
      } else {
        var a = string.toLowerCase().trim();
        return a.charAt(0).toUpperCase() + a.slice(1);
      }
    },    
}
  