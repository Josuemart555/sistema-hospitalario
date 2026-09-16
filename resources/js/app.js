import 'bootstrap';
import { createApp } from 'vue';
import PasswordStrength from './components/PasswordStrength.vue';

const components = { PasswordStrength };
document.querySelectorAll('[data-vue-component]').forEach((element) => {
    const component = components[element.dataset.vueComponent];
    if (component) createApp(component, JSON.parse(element.dataset.props || '{}')).mount(element);
});

document.querySelectorAll('[data-sidebar-toggle]').forEach((button) => button.addEventListener('click', () => document.querySelector('.app-sidebar')?.classList.toggle('show')));
