package com.sieuthithucung.controller;

import com.sieuthithucung.dto.UploadImageResponseDto;
import com.sieuthithucung.service.ImageStorageService;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.multipart.MultipartFile;

@RestController
@RequestMapping("/api/v1/uploads")
public class UploadController {

    private final ImageStorageService imageStorageService;

    public UploadController(ImageStorageService imageStorageService) {
        this.imageStorageService = imageStorageService;
    }

    @PostMapping("/images")
    public ResponseEntity<UploadImageResponseDto> uploadImage(@RequestParam("file") MultipartFile file) {
        String fileName = imageStorageService.storeImage(file);
        String publicPath = "/uploads/" + fileName;

        return ResponseEntity.status(HttpStatus.CREATED).body(UploadImageResponseDto.builder()
                .fileName(fileName)
                .path(publicPath)
                .url(publicPath)
                .build());
    }
}
