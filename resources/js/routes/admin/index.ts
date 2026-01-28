import logs from './logs'
import sync from './sync'

const admin = {
    logs: Object.assign(logs, logs),
    sync: Object.assign(sync, sync),
}

export default admin