// API Base URL
const API_BASE = '/api';
let currentUser = null;

// Initialize app
document.addEventListener('DOMContentLoaded', function() {
    loadFeaturedRecipes();
    loadCommunities();
    checkAuthStatus();
});

// Authentication
async function checkAuthStatus() {
    try {
        const response = await fetch(`${API_BASE}/auth/me`);
        if (response.ok) {
            currentUser = await response.json();
            updateAuthUI();
        }
    } catch (error) {
        console.log('User not authenticated');
    }
}

function updateAuthUI() {
    const profileLink = document.querySelector('a[href="#profile"]');
    if (profileLink && currentUser) {
        profileLink.textContent = `${currentUser.username}`;
        profileLink.href = '#';
    }
}

async function register() {
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    if (!username || !email || !password) {
        showAlert('Пожалуйста, заполните все поля', 'error');
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/auth/register`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, email, password })
        });

        const data = await response.json();

        if (response.ok) {
            showAlert('Регистрация успешна!', 'success');
            closeModal('registerModal');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.error || 'Ошибка регистрации', 'error');
        }
    } catch (error) {
        showAlert('Ошибка сервера', 'error');
    }
}

async function login() {
    const email = document.getElementById('loginEmail').value;
    const password = document.getElementById('loginPassword').value;

    if (!email || !password) {
        showAlert('Пожалуйста, заполните все поля', 'error');
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (response.ok) {
            showAlert('Вы успешно вошли!', 'success');
            closeModal('loginModal');
            currentUser = data.user;
            updateAuthUI();
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.error || 'Ошибка входа', 'error');
        }
    } catch (error) {
        showAlert('Ошибка сервера', 'error');
    }
}

async function logout() {
    try {
        await fetch(`${API_BASE}/auth/logout`, { method: 'POST' });
        currentUser = null;
        updateAuthUI();
        location.reload();
    } catch (error) {
        showAlert('Ошибка при выходе', 'error');
    }
}

// Recipes
async function loadFeaturedRecipes() {
    try {
        const response = await fetch(`${API_BASE}/recipes/popular`);
        const recipes = await response.json();

        const grid = document.getElementById('featured-recipes');
        if (!grid) return;

        grid.innerHTML = recipes.slice(0, 6).map(recipe => `
            <div class="recipe-card">
                ${recipe.image_url ? `<img src="${recipe.image_url}" alt="${recipe.title}" class="recipe-image">` : ''}
                <div class="recipe-content">
                    <h3 class="recipe-title">${recipe.title}</h3>
                    <div class="recipe-meta">
                        <span>⏱️ ${recipe.prep_time || 15} мин</span>
                        <span>👥 ${recipe.servings || 2}</span>
                        <span>🔥 ${recipe.difficulty_level || 'средняя'}</span>
                    </div>
                    <p>${recipe.description || 'Интересный рецепт'}</p>
                    <button class="btn btn-small" onclick="viewRecipe(${recipe.id})">Посмотреть</button>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading recipes:', error);
    }
}

async function createRecipe() {
    if (!currentUser) {
        showAlert('Вы должны быть залогинены', 'error');
        return;
    }

    const title = document.getElementById('recipeTitle').value;
    const description = document.getElementById('recipeDescription').value;
    const instructions = document.getElementById('recipeInstructions').value;

    if (!title) {
        showAlert('Введите название рецепта', 'error');
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/recipes`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, description, instructions })
        });

        if (response.ok) {
            showAlert('Рецепт создан!', 'success');
            closeModal('createRecipeModal');
            loadFeaturedRecipes();
        } else {
            showAlert('Ошибка при создании рецепта', 'error');
        }
    } catch (error) {
        showAlert('Ошибка сервера', 'error');
    }
}

function viewRecipe(id) {
    window.location.href = `/recipe/${id}`;
}

// Communities
async function loadCommunities() {
    try {
        const response = await fetch(`${API_BASE}/communities`);
        const data = await response.json();

        const grid = document.getElementById('communities');
        if (!grid) return;

        const communities = Array.isArray(data) ? data : data.communities || [];

        grid.innerHTML = communities.slice(0, 6).map(community => `
            <div class="tool-card">
                <div class="tool-content">
                    <h3>${community.name}</h3>
                    <p>${community.description || 'Интересное сообщество'}</p>
                    <p style="font-size: 0.9rem; color: #999;">👥 ${community.members_count || 0} участников</p>
                    <button class="btn btn-small" onclick="joinCommunity(${community.id})">Присоединиться</button>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading communities:', error);
    }
}

async function joinCommunity(id) {
    if (!currentUser) {
        showAlert('Вы должны быть залогинены', 'error');
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/communities/${id}/join`, {
            method: 'POST'
        });

        if (response.ok) {
            showAlert('Вы присоединились к сообществу!', 'success');
            loadCommunities();
        } else {
            showAlert('Ошибка при присоединении', 'error');
        }
    } catch (error) {
        showAlert('Ошибка сервера', 'error');
    }
}

async function createCommunity() {
    if (!currentUser) {
        showAlert('Вы должны быть залогинены', 'error');
        return;
    }

    const name = document.getElementById('communityName').value;
    const description = document.getElementById('communityDescription').value;

    if (!name) {
        showAlert('Введите название сообщества', 'error');
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/communities`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, description })
        });

        if (response.ok) {
            showAlert('Сообщество создано!', 'success');
            closeModal('createCommunityModal');
            loadCommunities();
        } else {
            showAlert('Ошибка при создании сообщества', 'error');
        }
    } catch (error) {
        showAlert('Ошибка сервера', 'error');
    }
}

// AI Tools
async function openAITool(tool) {
    if (!currentUser) {
        showAlert('Вы должны быть залогинены', 'error');
        return;
    }

    switch(tool) {
        case 'generator':
            showModal('recipeGeneratorModal');
            break;
        case 'scanner':
            showModal('foodScannerModal');
            break;
        case 'meal-plan':
            showModal('mealPlanModal');
            break;
        case 'advisor':
            showModal('cookingAdvisorModal');
            break;
    }
}

async function generateRecipeAI() {
    const diet = document.getElementById('dietType').value;
    const time = document.getElementById('cookTime').value;
    const ingredients = document.getElementById('ingredients').value;

    try {
        showLoading('Генерируем рецепт...');

        const response = await fetch(`${API_BASE}/ai/generate-recipe`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                diet,
                time,
                ingredients: ingredients.split(',').map(i => i.trim())
            })
        });

        const data = await response.json();

        if (response.ok) {
            displayRecipeResult(data.recipe);
        } else {
            showAlert('Ошибка при генерации', 'error');
        }
    } catch (error) {
        showAlert('Ошибка сервера', 'error');
    }
}

async function analyzeFood() {
    const imageUrl = document.getElementById('foodImage').value;

    if (!imageUrl) {
        showAlert('Укажите URL изображения', 'error');
        return;
    }

    try {
        showLoading('Анализируем блюдо...');

        const response = await fetch(`${API_BASE}/ai/analyze-food`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ image_url: imageUrl })
        });

        const data = await response.json();

        if (response.ok) {
            displayAnalysisResult(data.analysis);
        } else {
            showAlert('Ошибка при анализе', 'error');
        }
    } catch (error) {
        showAlert('Ошибка сервера', 'error');
    }
}

async function generateMealPlan() {
    const goal = document.getElementById('mealGoal').value;
    const calories = document.getElementById('dailyCalories').value;
    const restrictions = document.getElementById('restrictions').value;

    try {
        showLoading('Создаём план питания...');

        const response = await fetch(`${API_BASE}/ai/meal-plan`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ goal, daily_calories: calories, restrictions })
        });

        const data = await response.json();

        if (response.ok) {
            displayMealPlanResult(data.meal_plan);
        } else {
            showAlert('Ошибка при создании плана', 'error');
        }
    } catch (error) {
        showAlert('Ошибка сервера', 'error');
    }
}

// UI Helpers
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.add('active');
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.classList.remove('active');
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    document.body.insertBefore(alertDiv, document.body.firstChild);

    setTimeout(() => alertDiv.remove(), 3000);
}

function showLoading(message = 'Загружаем...') {
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'modal active';
    loadingDiv.id = 'loadingModal';
    loadingDiv.innerHTML = `
        <div class="loading">
            <div class="spinner"></div>
            <p>${message}</p>
        </div>
    `;
    document.body.appendChild(loadingDiv);
}

function hideLoading() {
    const loadingModal = document.getElementById('loadingModal');
    if (loadingModal) loadingModal.remove();
}

function displayRecipeResult(recipe) {
    hideLoading();
    showAlert('Рецепт создан!', 'success');
    console.log('Generated recipe:', recipe);
}

function displayAnalysisResult(analysis) {
    hideLoading();
    showAlert('Анализ завершён!', 'success');
    console.log('Food analysis:', analysis);
}

function displayMealPlanResult(mealPlan) {
    hideLoading();
    showAlert('План питания создан!', 'success');
    console.log('Meal plan:', mealPlan);
}

function navigateTo(path) {
    window.location.href = path;
}

// Close modals on background click
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.classList.remove('active');
    }
});

// Search functionality
function searchRecipes(query) {
    if (query.length < 2) return;

    fetch(`${API_BASE}/recipes/search/${query}`)
        .then(response => response.json())
        .then(recipes => {
            console.log('Search results:', recipes);
        })
        .catch(error => console.error('Search error:', error));
}

// Challenges
async function loadChallenges() {
    try {
        const response = await fetch(`${API_BASE}/challenges/active`);
        const challenges = await response.json();
        console.log('Active challenges:', challenges);
    } catch (error) {
        console.error('Error loading challenges:', error);
    }
}

async function joinChallenge(challengeId) {
    if (!currentUser) {
        showAlert('Вы должны быть залогинены', 'error');
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/challenges/${challengeId}/join`, {
            method: 'POST'
        });

        if (response.ok) {
            showAlert('Вы присоединились к испытанию!', 'success');
        } else {
            showAlert('Ошибка при присоединении', 'error');
        }
    } catch (error) {
        showAlert('Ошибка сервера', 'error');
    }
}
