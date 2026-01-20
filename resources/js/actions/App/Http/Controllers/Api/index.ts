import AuthController from './AuthController'
import DashboardController from './DashboardController'
import SalesOrderController from './SalesOrderController'
import CommissionController from './CommissionController'
import TeamController from './TeamController'
import ContactController from './ContactController'
import ProductController from './ProductController'

const Api = {
    AuthController: Object.assign(AuthController, AuthController),
    DashboardController: Object.assign(DashboardController, DashboardController),
    SalesOrderController: Object.assign(SalesOrderController, SalesOrderController),
    CommissionController: Object.assign(CommissionController, CommissionController),
    TeamController: Object.assign(TeamController, TeamController),
    ContactController: Object.assign(ContactController, ContactController),
    ProductController: Object.assign(ProductController, ProductController),
}

export default Api