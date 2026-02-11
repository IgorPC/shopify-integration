import { createApp, h, provide } from 'vue'
import App from './App.vue'
import { DefaultApolloClient } from '@vue/apollo-composable'
import { apolloClient } from './api/client'
import { router } from './router'
import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap/dist/js/bootstrap.bundle.min.js'

const app = createApp({
    setup() {
        provide(DefaultApolloClient, apolloClient)
    },
    render: () => h(App),
})

app.use(router)
app.mount('#app')
