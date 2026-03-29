function populateServiceTypes(category, preselectValue) {
    const serviceTypeSelect = document.getElementById('service-type');
    if (!serviceTypeSelect) return;
    serviceTypeSelect.innerHTML = '<option value="">Select a service type</option>';
    if (category && serviceTypes[category]) {
        serviceTypes[category].forEach(function (service) {
            const option = document.createElement('option');
            option.value = service.value;
            option.textContent = service.text;
            if (preselectValue && service.value === preselectValue) {
                option.selected = true;
            }
            serviceTypeSelect.appendChild(option);
        });
    }
}

const serviceTypes = {
    hair: [
        { value: 'hair_crop_wash', text: 'Hair Crop With Wash' },
        { value: 'hair_color', text: 'Hair Color' },
        { value: 'hair_crop_prince', text: 'Hair Crop Prince' },
        { value: 'hair_shower', text: 'Smooth Hair Shower' },
        { value: 'dandruff', text: 'Dandruff Control' },
        { value: 'buzzcut', text: 'Buzz Cut' },
        { value: 'fadecut', text: 'Fade Cut' },
        { value: 'frenchcut', text: 'French Cut' },
        { value: 'crewcut', text: 'Crew Cut' },
        { value: 'mohawakcut', text: 'Mohawak Cut' },
        { value: 'caesarcut', text: 'Ceaser Cut' },
        { value: 'Texturedcut', text: 'Textured Cut' }
    ],
    beard: [
        { value: 'stubble', text: 'Thick Stubble Beard' },
        { value: 'long_beard', text: 'Long Beard' },
        { value: 'short_beard', text: 'Short Beard' },
        { value: 'trimmed_beard', text: 'Trimmed Beard' },
        { value: 'fadebeard', text: 'Fade Beard' },
        { value: 'anchorbeard', text: 'Anchor Beard' },
        { value: 'bushybeard', text: 'Bushy Beard' },
        { value: 'goteebeard', text: 'Gotee Beard' }
    ],
    skin: [
        { value: 'men_facial', text: 'Mens Facial' },
        { value: 'brighting_facial', text: 'Brightening Facial' },
        { value: 'hydrafacial', text: 'Hydra Facial' },
        { value: 'collagen', text: 'Collagen Facial' },
        { value: 'chemical', text: 'Chemical Facial' },
        { value: 'charcol', text: 'Charcol Facial' },
        { value: 'deapclean', text: 'Deap Clean Facial' },
        { value: 'oxygen', text: 'Oxygen Facial' },
        { value: 'lesarskkin', text: 'Lesar Skin Resurfacing' }
    ],
    spa: [
        { value: 'fullbody', text: 'Full Body Exfoliation' },
        { value: 'fullhand', text: 'Full Hand Massage' },
        { value: 'head_shoulder', text: 'Head And Shoulder Massage' },
        { value: 'massage_wrap', text: 'Massage & Wrap' },
        { value: 'hotstone', text: 'Hot Stone Massage' },
        { value: 'tissue', text: 'Deep Tissue Massage' },
        { value: 'ayurvedic', text: 'Ayurvedic Massage' },
        { value: 'scrub', text: 'Body Scrub' },
        { value: 'hydrating', text: 'Hydrating Body Treatment' },
        { value: 'mud_wrap', text: 'Detoxifying Mud Wrap' },
        { value: 'cellulite', text: 'Cellulite Treatment' },
        { value: 'paraffin', text: 'Paraffin Body Treatment' }
    ]
};

function initAppointmentForm() {
    const service = document.getElementById('service-category');
    if (!service) return;

    service.addEventListener('change', function () {
        populateServiceTypes(this.value, '');
    });

    const typeSel = document.getElementById('service-type');
    const pre = typeSel && typeSel.dataset.preselect ? typeSel.dataset.preselect : '';
    if (service.value) {
        populateServiceTypes(service.value, pre);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAppointmentForm);
} else {
    initAppointmentForm();
}
