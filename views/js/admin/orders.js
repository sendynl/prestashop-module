/**
 * This file is part of the Sendy PrestaShop module - https://sendy.nl
 *
 * @author Sendy B.V.
 * @copyright Sendy B.V.
 * @license https://github.com/sendynl/prestashop-module/blob/master/LICENSE MIT
 *
 * @see https://github.com/sendynl/prestashop-module
 */

// Orders page entry point
$(function() {
    /**
     * @return {string[]}
     */
    const getSelectedOrderIds = () => Array.from(
        document.querySelectorAll('#order_filter_form input[name="order_orders_bulk[]"]:checked')
    ).map(element => element.value);

    $('.sendynl-print-label-bulk-action-submit-btn').on('click', function(e) {
        const url = new URL(window.sendynlRoutes.sendynl_orders_print_label, window.location.origin);
        for (const id of getSelectedOrderIds()) {
            url.searchParams.append('order_ids[]', id);
        }

        printOrDownloadBase64FromUri(url.toString());
    });

    $('.sendynl-print-label-single-action-btn').on('click', function(e) {
        const orderId = $(this).data('order-id');
        const url = new URL(window.sendynlRoutes.sendynl_orders_print_label, window.location.origin);
        url.searchParams.append('order_ids[]', orderId);

        printOrDownloadBase64FromUri(url.toString());
    });
});


// PDF printer
const printOrDownloadBase64FromUri = async (
    uri,
    {
        onDownloadClientError = (error) => {},
        onPrint = () => {},
        onPrintError = null,
        onPrintApiAvailableResult = (isAvailable) => {},
        onSuccess = () => {},
        filename = "",
    } = {},
) => {
    let printableDocument;
    const printableDocumentPromise = fetchDocument(uri);
    const checkPrintApiAvailabilityPromise = checkPrintApiAvailability();
    const printApiIsAvailable = await checkPrintApiAvailabilityPromise;
    onPrintApiAvailableResult(printApiIsAvailable);
    try {
        printableDocument = await printableDocumentPromise;
    } catch (error) {
        if (error instanceof PrintError && error.status >= 400 && error.status < 500) {
            onDownloadClientError(error);
            return;
        }
        throw error;
    }
    if (printApiIsAvailable) {
        try {
            await printDocument(printableDocument);
            onPrint();
        } catch (error) {
            if (onPrintError) {
                onPrintError(error);
            } else {
                throw error;
            }
        }
    } else {
        await downloadDocument(printableDocument, filename);
    }
    onSuccess();
};
const checkPrintApiAvailability = async () => {
    try {
        const response = await fetch("http://127.0.0.1:7639/health");
        if (!response.ok) {
            return false;
        }
        const responseData = await response.json();
        return responseData.name === "Sendy";
    } catch (error) {
        // Only log errors other than network errors. Network errors are expected when the app is not installed.
        if (!isNetworkError(error)) {
            console.error("Error checking app status:", error);
        }
        return false;
    }
};
/**
 * @throws {PrintError}
 */
const fetchDocument = async (uri) => {
    const response = await fetch(uri);
    if (!response.ok) {
        throw new PrintError("Failed to fetch document", response);
    }
    const responseData = await response.json();
    return {
        base64: responseData.labels ?? responseData.documents,
        token: response.headers.get("X-Sendy-Token") ?? "",
    };
};
/**
 * @throws {PrintError}
 */
const printDocument = async (printableDocument) => {
    const response = await fetch("http://127.0.0.1:7639/print", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + printableDocument.token,
        },
        body: JSON.stringify({
            label: printableDocument.base64,
        }),
    });
    if (!response.ok) {
        throw new PrintError("Failed to print document", response);
    }
    return response;
};
const downloadDocument = async (printableDocument, filename = "") => {
    const pdf = await fetch("data:application/pdf;base64," + printableDocument.base64);
    const blob = await pdf.blob();
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = filename || "labels.pdf";
    link.target = "_blank";
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
};
const isNetworkError = (error) =>
    error instanceof TypeError &&
    (error.message === "Failed to fetch" || error.message === "NetworkError when attempting to fetch resource.");
class PrintError extends Error {
    response;
    status;
    constructor(message, response) {
        super(`${message}: ${response.status} ${response.statusText}`);
        this.name = "PrintError";
        this.response = response;
        this.status = response.status;
    }
}
