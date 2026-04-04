package com.sieuthithucung.service.impl;

import com.sieuthithucung.service.ImageStorageService;
import org.springframework.beans.factory.annotation.Value;
import org.springframework.http.HttpStatus;
import org.springframework.stereotype.Service;
import org.springframework.web.multipart.MultipartFile;
import org.springframework.web.server.ResponseStatusException;

import java.io.IOException;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.Paths;
import java.nio.file.StandardCopyOption;
import java.util.Locale;
import java.util.UUID;

@Service
public class ImageStorageServiceImpl implements ImageStorageService {

    private final Path uploadRoot;

    public ImageStorageServiceImpl(@Value("${app.upload.dir:uploads}") String uploadDir) {
        this.uploadRoot = Paths.get(uploadDir).toAbsolutePath().normalize();
    }

    public String storeImage(MultipartFile file) {
        if (file == null || file.isEmpty()) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "Vui long chon file anh");
        }

        String contentType = file.getContentType();
        if (contentType == null || !contentType.toLowerCase(Locale.ROOT).startsWith("image/")) {
            throw new ResponseStatusException(HttpStatus.BAD_REQUEST, "Chi chap nhan file anh");
        }

        String originalName = file.getOriginalFilename();
        String extension = extractExtension(originalName);
        String fileName = UUID.randomUUID() + extension;

        try {
            Files.createDirectories(uploadRoot);
            Path target = uploadRoot.resolve(fileName);
            Files.copy(file.getInputStream(), target, StandardCopyOption.REPLACE_EXISTING);
            return fileName;
        } catch (IOException e) {
            throw new ResponseStatusException(HttpStatus.INTERNAL_SERVER_ERROR, "Khong the luu file anh", e);
        }
    }

    private String extractExtension(String fileName) {
        if (fileName == null) {
            return "";
        }

        int index = fileName.lastIndexOf('.');
        if (index < 0 || index == fileName.length() - 1) {
            return "";
        }

        return fileName.substring(index).toLowerCase(Locale.ROOT);
    }
}
