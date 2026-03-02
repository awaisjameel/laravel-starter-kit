import Api from './Api'
import Auth from './Auth'
import Dashboard from './Dashboard'
import Marketing from './Marketing'
import Settings from './Settings'
import Users from './Users'
const Modules = {
    Api: Object.assign(Api, Api),
    Marketing: Object.assign(Marketing, Marketing),
    Auth: Object.assign(Auth, Auth),
    Dashboard: Object.assign(Dashboard, Dashboard),
    Settings: Object.assign(Settings, Settings),
    Users: Object.assign(Users, Users)
}

export default Modules
