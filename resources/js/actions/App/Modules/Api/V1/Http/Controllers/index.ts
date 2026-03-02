import AdminUserController from './AdminUserController'
import MeController from './MeController'
const Controllers = {
    MeController: Object.assign(MeController, MeController),
    AdminUserController: Object.assign(AdminUserController, AdminUserController)
}

export default Controllers
