export default (message, el = null, showError = false) => {
    let payload = {
        message,
        element: el,
    };

    if (!showError) throw payload;

    console.error(payload);
};
