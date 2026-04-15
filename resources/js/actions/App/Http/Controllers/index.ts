import Api from './Api'
import Web from './Web'
import Settings from './Settings'
import SalesOrderController from './SalesOrderController'
import ProductStockController from './ProductStockController'
import ContactController from './ContactController'
import ProductController from './ProductController'
import CommissionController from './CommissionController'
import UserController from './UserController'
import RoleController from './RoleController'
import PermissionController from './PermissionController'
import MyTeamController from './MyTeamController'
import TeamController from './TeamController'
import Admin from './Admin'
import OdooController from './OdooController'

const Controllers = {
    Api: Object.assign(Api, Api),
    Web: Object.assign(Web, Web),
    Settings: Object.assign(Settings, Settings),
    SalesOrderController: Object.assign(SalesOrderController, SalesOrderController),
    ProductStockController: Object.assign(ProductStockController, ProductStockController),
    ContactController: Object.assign(ContactController, ContactController),
    ProductController: Object.assign(ProductController, ProductController),
    CommissionController: Object.assign(CommissionController, CommissionController),
    UserController: Object.assign(UserController, UserController),
    RoleController: Object.assign(RoleController, RoleController),
    PermissionController: Object.assign(PermissionController, PermissionController),
    MyTeamController: Object.assign(MyTeamController, MyTeamController),
    TeamController: Object.assign(TeamController, TeamController),
    Admin: Object.assign(Admin, Admin),
    OdooController: Object.assign(OdooController, OdooController),
}

export default Controllers