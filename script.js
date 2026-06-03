const feedbackModal = document.getElementById('feedbackModal');
const thankModal = document.getElementById('thankModal');
const feedbackText = document.getElementById('feedbackText');
const feedbackError = document.getElementById('feedbackError');
const thankMessage = document.getElementById('thankMessage');
const sendFeedbackButton = document.getElementById('sendFeedback');
const voteButtons = document.querySelectorAll('.vote-button');

function openModal(modal) {
    modal.classList.add('open');
}

function closeModal(modal) {
    modal.classList.remove('open');
}

function setActiveVote(vote) {
    voteButtons.forEach((button) => {
        button.classList.toggle('active', button.dataset.vote === vote);
    });
}

async function saveFeedback(vote, feedback = '') {
    const formData = new FormData();
    formData.append('action', 'save_feedback');
    formData.append('vote', vote);
    formData.append('feedback', feedback);

    const response = await fetch('feedback.php', {
        method: 'POST',
        body: formData,
    });

    let result;
    try {
        result = await response.json();
    } catch (error) {
        throw new Error('Server ei tagastanud korrektset JSON-vastust.');
    }

    if (!response.ok || !result.success) {
        throw new Error(result.message || 'Salvestamine ebaõnnestus.');
    }

    return result;
}

voteButtons.forEach((button) => {
    button.addEventListener('click', async () => {
        const vote = button.dataset.vote;
        setActiveVote(vote);

        if (vote === 'dislike') {
            feedbackError.textContent = '';
            feedbackText.value = '';
            openModal(feedbackModal);
            feedbackText.focus();
            return;
        }

        try {
            await saveFeedback('like');
            thankMessage.textContent = 'Aitäh! Sinu like on salvestatud.';
            openModal(thankModal);
        } catch (error) {
            thankMessage.textContent = error.message;
            openModal(thankModal);
        }
    });
});

sendFeedbackButton.addEventListener('click', async () => {
    const text = feedbackText.value.trim();

    if (text.length === 0) {
        feedbackError.textContent = 'Palun kirjuta tagasiside.';
        feedbackText.focus();
        return;
    }

    feedbackError.textContent = '';
    sendFeedbackButton.disabled = true;
    sendFeedbackButton.textContent = 'Saadan...';

    try {
        await saveFeedback('dislike', text);
        closeModal(feedbackModal);
        feedbackText.value = '';
        thankMessage.textContent = 'Aitäh! Sinu tagasiside on vastu võetud.';
        openModal(thankModal);
    } catch (error) {
        feedbackError.textContent = error.message;
    } finally {
        sendFeedbackButton.disabled = false;
        sendFeedbackButton.textContent = 'Saada';
    }
});

document.getElementById('closeFeedback').addEventListener('click', () => closeModal(feedbackModal));
document.getElementById('closeThank').addEventListener('click', () => closeModal(thankModal));
document.getElementById('closeThankButton').addEventListener('click', () => closeModal(thankModal));

window.addEventListener('click', (event) => {
    if (event.target === feedbackModal) closeModal(feedbackModal);
    if (event.target === thankModal) closeModal(thankModal);
});

window.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        closeModal(feedbackModal);
        closeModal(thankModal);
    }
});
