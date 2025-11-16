import React, { useState, useEffect, useRef } from 'react';
import { MapPinIcon, XMarkIcon, ChevronDownIcon } from '@heroicons/react/24/outline';
import axios from 'axios';

function CityFilter({ onCityChange, selectedCity = '' }) {
    const [cities, setCities] = useState([]);
    const [loading, setLoading] = useState(false);
    const [isOpen, setIsOpen] = useState(false);
    const [searchTerm, setSearchTerm] = useState('');
    const [filteredCities, setFilteredCities] = useState([]);
    const dropdownRef = useRef(null);

    useEffect(() => {
        fetchCities();
        // Close dropdown when clicking outside
        const handleClickOutside = (event) => {
            if (dropdownRef.current && !dropdownRef.current.contains(event.target)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    useEffect(() => {
        if (searchTerm === '') {
            setFilteredCities(cities);
        } else {
            const filtered = cities.filter(city =>
                city.toLowerCase().includes(searchTerm.toLowerCase())
            );
            setFilteredCities(filtered);
        }
    }, [searchTerm, cities]);

    const fetchCities = async () => {
        try {
            setLoading(true);
            const response = await axios.get('/api/cities');
            if (response.data && response.data.success) {
                setCities(response.data.cities || []);
                setFilteredCities(response.data.cities || []);
            }
        } catch (error) {
            console.error('Error fetching cities:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleCitySelect = (city) => {
        setSearchTerm(city);
        setIsOpen(false);
        localStorage.setItem('selectedCity', city);
        if (onCityChange) {
            onCityChange(city);
        }
    };

    const handleClear = () => {
        setSearchTerm('');
        setIsOpen(false);
        localStorage.removeItem('selectedCity');
        if (onCityChange) {
            onCityChange('');
        }
    };

    useEffect(() => {
        if (selectedCity) {
            setSearchTerm(selectedCity);
        }
    }, [selectedCity]);

    return (
        <div className="relative" ref={dropdownRef}>
            <div className="relative">
                <div className="flex items-center space-x-2 bg-white border border-gray-300 rounded-lg hover:border-green-500 transition-colors">
                    <MapPinIcon className="h-5 w-5 text-gray-400 ml-3" />
                    <input
                        type="text"
                        value={searchTerm}
                        onChange={(e) => {
                            setSearchTerm(e.target.value);
                            setIsOpen(true);
                        }}
                        onFocus={() => setIsOpen(true)}
                        placeholder="Select or type city..."
                        className="flex-1 px-3 py-2 outline-none rounded-lg text-sm min-w-[120px] sm:min-w-[200px] w-full sm:w-auto"
                    />
                    {searchTerm && (
                        <button
                            onClick={handleClear}
                            className="p-1 hover:bg-gray-100 rounded-full transition-colors mr-1"
                            title="Clear"
                        >
                            <XMarkIcon className="h-4 w-4 text-gray-400" />
                        </button>
                    )}
                    <button
                        onClick={() => setIsOpen(!isOpen)}
                        className="p-2 hover:bg-gray-100 rounded-r-lg transition-colors"
                    >
                        <ChevronDownIcon className={`h-4 w-4 text-gray-400 transition-transform ${isOpen ? 'transform rotate-180' : ''}`} />
                    </button>
                </div>

                {/* Dropdown */}
                {isOpen && (
                    <div className="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-auto">
                        {loading ? (
                            <div className="p-4 text-center text-gray-500 text-sm">
                                Loading cities...
                            </div>
                        ) : filteredCities.length === 0 ? (
                            <div className="p-4 text-center text-gray-500 text-sm">
                                No cities found
                            </div>
                        ) : (
                            <>
                                <div className="p-2 border-b border-gray-200">
                                    <button
                                        onClick={() => handleCitySelect('')}
                                        className="w-full text-left px-3 py-2 text-sm hover:bg-green-50 hover:text-green-600 rounded transition-colors"
                                    >
                                        All Cities
                                    </button>
                                </div>
                                <div className="max-h-48 overflow-auto">
                                    {filteredCities.map((city, index) => (
                                        <button
                                            key={index}
                                            onClick={() => handleCitySelect(city)}
                                            className={`w-full text-left px-3 py-2 text-sm hover:bg-green-50 transition-colors ${
                                                searchTerm === city ? 'bg-green-50 text-green-600' : ''
                                            }`}
                                        >
                                            {city}
                                        </button>
                                    ))}
                                </div>
                            </>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}

export default CityFilter;
