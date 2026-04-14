# Instructions

## Step 1 — Install Python

## Step 2 — Install required Python package

```bash
pip install requests mysql-connector-python markdown
```

---

## Step 3 — Install Ollama

## Step 4 — Pull your AI models

```bash
ollama pull llama3.2:1b
ollama pull mistral:7b
ollama pull todorov/bggpt:Gemma-3-**12B**-IT-Q4_K_M
```
*or smaller, faster model:* 
```bash
ollama pull todorov/bggpt:Gemma-3-4B-IT-Q4_K_M
```
*and change the generate_post.py to use it.* 

---

## Step 5 — Start Ollama server, leave it open

```bash
ollama serve
```
*Or if the icon is on your taskbar - it is running!* 

---

## Step 6 — Generate a Story

```bash
ai_blog_generator/generate post.bat
```

*Publish your story in https://localhost/Local-AI-Blogger/admin.php* 

---