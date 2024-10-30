const payload = {
    publicKey: klp_payment_params.primary_key,
    data: {
        amount: parseFloat(klp_payment_params.amount, 10),
        currency: klp_payment_params.currency,
        merchant_reference: klp_payment_params.txnref,
        meta_data: {
            customer: klp_payment_params.firstname + ' ' + klp_payment_params.lastname,
            email: klp_payment_params.email,
            order_id: klp_payment_params.order_id,
            klump_plugin_source: 'woocommerce',
            klump_plugin_version: '1.3.5',
        },
        items: klp_payment_params.order_items,
        redirect_url: klp_payment_params.cb_url,
    },
    onSuccess: (data) => {
        transactionComplete({
            order_id: klp_payment_params.order_id,
            cb_url: klp_payment_params.cb_url,
            ...data.data.data.data
        })
        return data;
    },
    onError: (data) => {
        console.error('Klump Gateway Error has occurred.')
    },
    onLoad: (data) => {
    },
    onOpen: (data) => {
    },
    onClose: (data) => {
    }
}

if (klp_payment_params.firstname) {
    payload.data.first_name = klp_payment_params.firstname;
}

if (klp_payment_params.lastname) {
    payload.data.last_name = klp_payment_params.lastname;
}

if (klp_payment_params.email) {
    payload.data.email = klp_payment_params.email;
}

if (klp_payment_params.phone) {
    if (klp_payment_params.phone.length > 11) {
        payload.data.phone = klp_payment_params.phone.substring(klp_payment_params.phone.length - 10);
        payload.data.phone = '0' + payload.data.phone;
    } else {
        payload.data.phone = klp_payment_params.phone;
    }
}

if (klp_payment_params.shipping_fee !== '0' && klp_payment_params.shipping_fee > 0) {
    payload.data.shipping_fee = parseFloat(klp_payment_params.shipping_fee, 10);
}

if (klp_payment_params.discount && klp_payment_params.discount !== '0' && klp_payment_params.discount > 0) {
    payload.data.discount = parseFloat(klp_payment_params.discount, 10);
}

document.getElementById('klump__checkout').addEventListener('click', function () {
    const klump = new Klump(payload);
});

function transactionComplete(data) {
    const form = document.createElement("form");
    form.setAttribute("method", "POST");
    form.setAttribute("action", data.cb_url);

    for (let item in data) {
        if (item === 'cb_url') {
            continue;
        }
        const field = document.createElement("input");
        field.setAttribute("type", "hidden");
        field.setAttribute("name", item);
        field.setAttribute("value", data[item]);
        form.appendChild(field);
    }

    document.body.appendChild(form);
    form.submit();
}
