import { Alpine } from 'alpinejs'
import { debounce } from './utils'

console.log('Component initialized');
Alpine.data('myComponent', () => ({
    init() {
        debounce(() => console.log('Debounced'), 100);
    }
}));
