document.addEventListener('DOMContentLoaded', function () {
    let sendEmailsTimeout;

    // Handle file upload form submission
    document.getElementById('uploadForm').addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const xhr = new XMLHttpRequest();

        xhr.open('POST', 'backend/upload.php', true);

        // Display progress
        xhr.upload.addEventListener('progress', function (e) {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                const progressBar = document.getElementById('progressBar');
                const progressStatus = document.getElementById('progressStatus');
                progressBar.style.display = 'block';
                progressBar.value = percentComplete;
                progressStatus.textContent = `Uploading: ${Math.round(percentComplete)}%`;
            }
        });

        xhr.onload = function () {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                displayMessage(response.message || response.error || 'Unknown error occurred');
            } else {
                displayMessage('Upload failed');
            }
            // Hide progress indicator after upload
            document.getElementById('progressBar').style.display = 'none';
            document.getElementById('progressStatus').textContent = '';
        };

        xhr.onerror = function () {
            displayMessage('Upload failed');
        };

        xhr.send(formData);
    });

    // Handle send emails form submission
    document.getElementById('sendEmailsForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        sendEmails(formData);
    });

    /**
     * Sends emails using the provided form data.
     *
     * @param {FormData} formData The form data to be sent.
     */
    function sendEmails(formData) {
        fetch('backend/send_emails.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                handleSendEmailsResponse(data);
            })
            .catch(error => {
                console.error('Error:', error);
                displayMessage('An error occurred while sending emails.');
            });
    }

    /**
     * Handles the response from send emails request.
     *
     * @param {Object} data The response data from the server.
     */
    function handleSendEmailsResponse(data) {
        const messageDiv = document.getElementById('message');
        const emailProgressBar = document.getElementById('emailProgressBar');
        const emailProgressStatus = document.getElementById('emailProgressStatus');

        if (data.message) {
            displayMessage(data.message);
        }
        if (data.emails && data.emails.length > 0) {
            const totalEmails = data.totalEmails;
            const sentEmails = data.sentEmails;

            emailProgressBar.style.display = 'block';
            emailProgressBar.value = (sentEmails / totalEmails) * 100;
            emailProgressStatus.textContent = `Emails sent: ${sentEmails} of ${totalEmails}`;

            const emailList = document.createElement('ul');
            data.emails.forEach(function (email) {
                const listItem = document.createElement('li');
                listItem.textContent = email.message;
                if (email.status === 'error') {
                    listItem.classList.add('error');
                }
                emailList.appendChild(listItem);
            });
            messageDiv.appendChild(emailList);
        }
        if (data.interval) {
            sendEmailsTimeout = setTimeout(function () {
                sendEmails(new FormData(document.getElementById('sendEmailsForm')));
            }, data.interval * 1000);
        }
    }

    // Handle stop button click event
    document.getElementById('stopButton').addEventListener('click', function () {
        clearTimeout(sendEmailsTimeout);
        displayMessage('Sending emails stopped.');
    });

    /**
     * Displays a message in the message div.
     *
     * @param {string} message The message to be displayed.
     */
    function displayMessage(message) {
        const messageDiv = document.getElementById('message');
        messageDiv.innerHTML = message;
    }
});
