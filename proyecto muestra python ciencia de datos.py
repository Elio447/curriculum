# ==============================================================================
# PROYECTO DE CIENCIA DE DATOS: ANÁLISIS DE SENTIMIENTOS DE RESEÑAS
# Este script demuestra un flujo de trabajo básico para un proyecto de ciencia de
# datos usando Python.
# ==============================================================================

# ==============================================================================
# 0. INSTALAR LIBRERÍAS (si no están instaladas)
# ------------------------------------------------------------------------------
# Esta sección garantiza que todas las bibliotecas necesarias para ejecutar el
# script estén instaladas. Esto es útil para compartir el proyecto y asegurar
# que cualquier persona pueda ejecutarlo sin errores de "módulo no encontrado".

import subprocess
import sys

def install_and_import(package):
    try:
        __import__(package)
    except ImportError:
        print(f"Instalando {package}...")
        subprocess.check_call([sys.executable, "-m", "pip", "install", package])
        __import__(package)

# Lista de librerías necesarias
required_packages = [
    'pandas',
    'numpy',
    'matplotlib',
    'seaborn',
    'scikit-learn',
    'nltk'
]

for package in required_packages:
    install_and_import(package)

print(">>> Todas las librerías necesarias están instaladas y listas.")

# 1. IMPORTAR LIBRERÍAS
# ------------------------------------------------------------------------------
# Importamos las bibliotecas necesarias para la manipulación de datos,
# visualización, preprocesamiento de texto y modelado de machine learning.
# Estas son las librerías principales de un científico de datos.

import pandas as pd
import numpy as np
import re
import matplotlib.pyplot as plt
import seaborn as sns
from sklearn.model_selection import train_test_split
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.naive_bayes import MultinomialNB
from sklearn.metrics import accuracy_score, classification_report
import nltk
from nltk.corpus import stopwords

# Descarga las stopwords de NLTK (solo necesitas hacerlo una vez)
try:
    nltk.data.find('corpora/stopwords')
except nltk.downloader.DownloadError:
    print("Descargando NLTK stopwords...")
    nltk.download('stopwords')
    print("Descarga completa.")

# ==============================================================================
# 2. GENERACIÓN Y LIMPIEZA DE DATOS
# ------------------------------------------------------------------------------
# Para este ejemplo, creamos un conjunto de datos ficticio de reseñas y
# su sentimiento. En un proyecto real, esto sería el resultado del web scraping
# o la carga de un archivo CSV.

print(">>> 1. Creando un conjunto de datos de ejemplo...")

# Creamos un DataFrame de pandas con reseñas y sus sentimientos
data = {
    'review': [
        '¡Este producto es fantástico! Lo recomiendo mucho.',
        'El servicio al cliente fue terrible, no volveré a comprar.',
        'La calidad es decente, pero el envío tardó una semana.',
        'Estoy muy contento con mi compra, el precio fue excelente.',
        'No funciona como esperaba, estoy decepcionado.',
        'Las características son buenas, pero el diseño no me gusta.',
        'Una experiencia de compra maravillosa, ¡superó mis expectativas!',
        'No sé cómo usarlo, las instrucciones son muy confusas.',
        'Totalmente satisfecho con la calidad y el rendimiento.',
        'El peor producto que he comprado en años.'
    ],
    'sentiment': ['positive', 'negative', 'neutral', 'positive', 'negative', 'neutral', 'positive', 'negative', 'positive', 'negative']
}
df = pd.DataFrame(data)

print("Datos de ejemplo:")
print(df.head())

# Función para limpiar el texto: eliminar caracteres especiales,
# convertir a minúsculas y eliminar stopwords.
def clean_text(text):
    text = text.lower()
    text = re.sub(r'[^a-záéíóúüñ\s]', '', text)
    stop_words = set(stopwords.words('spanish'))
    words = text.split()
    filtered_words = [word for word in words if word not in stop_words]
    return " ".join(filtered_words)

print("\n>>> 2. Limpiando el texto de las reseñas...")
df['cleaned_review'] = df['review'].apply(clean_text)
print("Reseñas limpias (primeras 5):")
print(df['cleaned_review'].head())

# ==============================================================================
# 3. ANÁLISIS EXPLORATORIO DE DATOS (EDA)
# ------------------------------------------------------------------------------
# Visualizamos la distribución de los sentimientos para entender mejor los datos.

print("\n>>> 3. Realizando el Análisis Exploratorio de Datos (EDA)...")
plt.figure(figsize=(8, 6))
sns.countplot(x='sentiment', data=df)
plt.title('Distribución de Sentimientos en las Reseñas')
plt.xlabel('Sentimiento')
plt.ylabel('Cantidad')
plt.show()

# ==============================================================================
# 4. MODELADO PREDICTIVO
# ------------------------------------------------------------------------------
# Preparamos los datos para el modelo y entrenamos un clasificador Naive Bayes.

print("\n>>> 4. Preparando los datos para el modelo...")
# Dividimos los datos en conjuntos de entrenamiento y prueba
X_train, X_test, y_train, y_test = train_test_split(
    df['cleaned_review'],
    df['sentiment'],
    test_size=0.3,
    random_state=42
)

# Convertimos el texto en vectores numéricos usando TF-IDF
vectorizer = TfidfVectorizer()
X_train_vectorized = vectorizer.fit_transform(X_train)
X_test_vectorized = vectorizer.transform(X_test)

# Entrenamos el modelo
print("\n>>> 5. Entrenando el modelo de Naive Bayes...")
model = MultinomialNB()
model.fit(X_train_vectorized, y_train)

# ==============================================================================
# 5. EVALUACIÓN DEL MODELO
# ------------------------------------------------------------------------------
# Evaluamos el rendimiento del modelo con el conjunto de prueba.

print("\n>>> 6. Evaluando el modelo...")
predictions = model.predict(X_test_vectorized)
accuracy = accuracy_score(y_test, predictions)
report = classification_report(y_test, predictions)

print(f"Precisión del modelo: {accuracy:.2f}")
print("Reporte de clasificación:")
print(report)

# ==============================================================================
# 6. EJEMPLO DE PREDICCIÓN
# ------------------------------------------------------------------------------
# Usamos el modelo entrenado para predecir el sentimiento de una nueva reseña.

print("\n>>> 7. Prediciendo el sentimiento de una nueva reseña...")
new_review = ["Este es un producto muy malo, no lo volvería a comprar."]
cleaned_new_review = [clean_text(new_review[0])]
new_review_vectorized = vectorizer.transform(cleaned_new_review)
prediction = model.predict(new_review_vectorized)

print(f"\nReseña: '{new_review[0]}'")
print(f"Predicción del modelo: '{prediction[0]}'")
