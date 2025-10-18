import replicate
import requests

input_data = {
    "prompt": "Make the sheets in the style of the logo. Make the scene natural.",
    "image_input": ""
}

# Run the model
output_url = replicate.run(
    "google/nano-banana",
    input=input_data
)

print("Generated image URL:", output_url)

# Download the image
response = requests.get(output_url)
with open("output.jpg", "wb") as f:
    f.write(response.content)

print("Image saved as output.jpg")
