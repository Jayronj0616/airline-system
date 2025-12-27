const airports = [
    { code: 'MNL', name: 'Manila - Ninoy Aquino International', city: 'Manila', country: 'Philippines' },
    { code: 'CEB', name: 'Cebu - Mactan-Cebu International', city: 'Cebu', country: 'Philippines' },
    { code: 'DVO', name: 'Davao - Francisco Bangoy International', city: 'Davao', country: 'Philippines' },
    { code: 'ILO', name: 'Iloilo - Iloilo International', city: 'Iloilo', country: 'Philippines' },
    { code: 'BCD', name: 'Bacolod - Bacolod-Silay', city: 'Bacolod', country: 'Philippines' },
    { code: 'CRK', name: 'Clark - Clark International', city: 'Angeles', country: 'Philippines' },
    
    { code: 'HKG', name: 'Hong Kong International', city: 'Hong Kong', country: 'Hong Kong' },
    { code: 'SIN', name: 'Singapore Changi', city: 'Singapore', country: 'Singapore' },
    { code: 'BKK', name: 'Bangkok - Suvarnabhumi', city: 'Bangkok', country: 'Thailand' },
    { code: 'KUL', name: 'Kuala Lumpur International', city: 'Kuala Lumpur', country: 'Malaysia' },
    { code: 'TPE', name: 'Taipei Taoyuan International', city: 'Taipei', country: 'Taiwan' },
    { code: 'ICN', name: 'Seoul Incheon International', city: 'Seoul', country: 'South Korea' },
    { code: 'NRT', name: 'Tokyo Narita International', city: 'Tokyo', country: 'Japan' },
    { code: 'PVG', name: 'Shanghai Pudong International', city: 'Shanghai', country: 'China' },
    { code: 'HAN', name: 'Hanoi - Noi Bai International', city: 'Hanoi', country: 'Vietnam' },
    { code: 'SGN', name: 'Ho Chi Minh City - Tan Son Nhat', city: 'Ho Chi Minh City', country: 'Vietnam' },
    { code: 'CGK', name: 'Jakarta - Soekarno-Hatta International', city: 'Jakarta', country: 'Indonesia' },
    { code: 'DPS', name: 'Bali - Ngurah Rai International', city: 'Denpasar', country: 'Indonesia' },
    
    { code: 'DXB', name: 'Dubai International', city: 'Dubai', country: 'UAE' },
    { code: 'DOH', name: 'Doha - Hamad International', city: 'Doha', country: 'Qatar' },
    
    { code: 'SYD', name: 'Sydney - Kingsford Smith', city: 'Sydney', country: 'Australia' },
    { code: 'MEL', name: 'Melbourne Airport', city: 'Melbourne', country: 'Australia' },
    
    { code: 'LAX', name: 'Los Angeles International', city: 'Los Angeles', country: 'USA' },
    { code: 'SFO', name: 'San Francisco International', city: 'San Francisco', country: 'USA' },
];

function createAutocomplete(inputElement) {
    const dropdown = document.createElement('div');
    dropdown.className = 'absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto hidden';
    inputElement.parentElement.appendChild(dropdown);
    
    inputElement.addEventListener('input', function() {
        const value = this.value.toLowerCase().trim();
        
        if (value.length < 1) {
            dropdown.classList.add('hidden');
            return;
        }
        
        const filtered = airports.filter(airport => 
            airport.code.toLowerCase().includes(value) ||
            airport.name.toLowerCase().includes(value) ||
            airport.city.toLowerCase().includes(value) ||
            airport.country.toLowerCase().includes(value)
        );
        
        if (filtered.length === 0) {
            dropdown.classList.add('hidden');
            return;
        }
        
        dropdown.innerHTML = '';
        filtered.slice(0, 8).forEach(airport => {
            const item = document.createElement('div');
            item.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 last:border-b-0';
            item.innerHTML = `
                <div class="flex justify-between items-start">
                    <div>
                        <p class="font-semibold text-gray-900">${airport.city}</p>
                        <p class="text-sm text-gray-600">${airport.name}</p>
                    </div>
                    <span class="text-sm font-bold text-blue-600">${airport.code}</span>
                </div>
            `;
            
            item.addEventListener('click', function() {
                inputElement.value = airport.code;
                dropdown.classList.add('hidden');
            });
            
            dropdown.appendChild(item);
        });
        
        dropdown.classList.remove('hidden');
    });
    
    inputElement.addEventListener('focus', function() {
        if (this.value.trim().length >= 1) {
            this.dispatchEvent(new Event('input'));
        }
    });
    
    document.addEventListener('click', function(e) {
        if (!inputElement.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('hidden');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const originInput = document.querySelector('input[name="origin"]');
    const destinationInput = document.querySelector('input[name="destination"]');
    
    if (originInput) createAutocomplete(originInput);
    if (destinationInput) createAutocomplete(destinationInput);
});
