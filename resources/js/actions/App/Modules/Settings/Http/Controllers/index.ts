import PasswordController from './PasswordController'
import ProfileController from './ProfileController'
const Controllers = {
    ProfileController: Object.assign(ProfileController, ProfileController),
    PasswordController: Object.assign(PasswordController, PasswordController)
}

export default Controllers
