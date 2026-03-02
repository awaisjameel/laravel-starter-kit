import admin from './admin'
import me from './me'
const v1 = {
    me: Object.assign(me, me),
    admin: Object.assign(admin, admin)
}

export default v1
