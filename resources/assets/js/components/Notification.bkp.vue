<template>
    <li class="dropdown">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-bell"></i>
          <!-- <span class="badge">{{unreadNotifications.length}}</span> -->
        </a>
        <ul class="dropdown-menu alert-dropdown">
          <li>
            <notification-item v-for="unread in unreadNotifications"></notification-item>
          </li>

        </ul>
    </li>
</template>

<script>
  // import NotificationItem from './NotificationItem.vue'
    export default {
        props:{
                  unreads:{
                    type: Array
                  },
                  userid: {
                      type: String
                  }
              },
        data(){
          return {
            unreadNotifications: this.tipe
          }
        },
        mounted() {
            console.log('Component mounted.');
            Echo.private('App.User.' + this.userid)
                .notification((notification) => {
                    console.log(notification);
                    let newUnreadNotifications={data:{tipe:notification.tipe,subject:notification.subject}};
                    this.unreadNotifications.push(newUnreadNotifications);
                });

        }
    }
</script>
