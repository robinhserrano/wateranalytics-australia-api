import SyncLogController from './SyncLogController'
import SyncController from './SyncController'

const Admin = {
    SyncLogController: Object.assign(SyncLogController, SyncLogController),
    SyncController: Object.assign(SyncController, SyncController),
}

export default Admin