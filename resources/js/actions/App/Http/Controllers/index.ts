import Auth from './Auth'
import Settings from './Settings'
import UserController from './UserController'
const Controllers = {
    UserController: Object.assign(UserController, UserController),
    Settings: Object.assign(Settings, Settings),
    Auth: Object.assign(Auth, Auth)
}

export default Controllers
