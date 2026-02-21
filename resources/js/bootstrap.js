import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response && (error.response.status === 401 || error.response.status === 419)) {
            const currentPath = window.location.pathname;

            if (currentPath.startsWith('/admin')) {
                // 管理画面：ログインページへ
                alert('セッションが切れました。ログイン画面に戻ります。');
                window.location.href = '/admin/login';
            } else {
                // 一般登録画面：リロードしてLaravelの判定（members.resendへの遷移）に任せる
                alert('有効期限が切れたか、長時間放置されました。状態を確認します。');
                window.location.reload();
            }
        }
        return Promise.reject(error);
    }
)
