package com.sieuthithucung.controller;

import com.sieuthithucung.dto.PasswordResetTokenDto;
import com.sieuthithucung.service.PasswordResetTokenService;
import org.springframework.http.HttpStatus;
import org.springframework.http.ResponseEntity;
import java.util.List;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/password-reset-tokens")
public class PasswordResetTokenController {

    private final PasswordResetTokenService service;

    public PasswordResetTokenController(PasswordResetTokenService service) {
        this.service = service;
    }

    @PostMapping
    public ResponseEntity<PasswordResetTokenDto> create(@RequestBody PasswordResetTokenDto dto) {
        return ResponseEntity.status(HttpStatus.CREATED).body(service.create(dto));
    }

    @PutMapping("/{id}")
    public ResponseEntity<PasswordResetTokenDto> update(@PathVariable String id, @RequestBody PasswordResetTokenDto dto) {
        return ResponseEntity.ok(service.update(id, dto));
    }

    @DeleteMapping("/{id}")
    public ResponseEntity<Void> delete(@PathVariable String id) {
        service.delete(id);
        return ResponseEntity.noContent().build();
    }

    @GetMapping("/{id}")
    public ResponseEntity<PasswordResetTokenDto> findById(@PathVariable String id) {
        return ResponseEntity.ok(service.findById(id));
    }

    @GetMapping
    public ResponseEntity<List<PasswordResetTokenDto>> getAll() {
        return ResponseEntity.ok(service.getAll());
    }
}